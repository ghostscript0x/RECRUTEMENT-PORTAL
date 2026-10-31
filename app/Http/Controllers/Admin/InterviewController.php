<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Interview;
use App\Services\InterviewInvitationDispatcher;
use App\Services\StatusTransitioner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class InterviewController extends Controller
{
    public function index(): View
    {
        $applications = Application::query()
            ->whereIn('status', [ApplicationStatus::Shortlisted->value, ApplicationStatus::Interview->value])
            ->with(['position:id,title', 'interviews'])
            ->orderByDesc('submitted_at')
            ->paginate(config('recruitment.per_page'));

        return view('admin.interviews.index', [
            'applications' => $applications,
            'timezone' => config('recruitment.timezone'),
        ]);
    }

    public function edit(Application $application): View
    {
        abort_unless(in_array($application->status, [
            ApplicationStatus::Shortlisted->value,
            ApplicationStatus::Interview->value,
        ], true), 404);

        return view('admin.interviews.edit', [
            'application' => $application->load('position'),
            'interview' => $application->interviews()->first(),
            'timezone' => config('recruitment.timezone'),
        ]);
    }

    public function store(
        Request $request,
        Application $application,
        StatusTransitioner $transitioner,
        InterviewInvitationDispatcher $invitations,
    ): RedirectResponse {
        abort_unless(in_array($application->status, [
            ApplicationStatus::Shortlisted->value,
            ApplicationStatus::Interview->value,
        ], true), 404);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'duration_minutes' => ['required', 'integer', 'in:15,30,45,60,90,120'],
            'mode' => ['required', Rule::in(array_keys(Interview::CHANNELS))],
            'location' => ['required', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'email_subject' => ['required', 'string', 'max:200'],
            'email_message' => ['required', 'string', 'min:5', 'max:5000'],
            'action' => ['required', Rule::in(['save', 'send'])],
        ], attributes: [
            'scheduled_at' => 'Interview date and time',
            'duration_minutes' => 'Interview duration',
            'mode' => 'Interview contact method',
            'location' => 'Contact details or venue',
            'instructions' => 'Interview instructions',
            'email_subject' => 'Email subject',
            'email_message' => 'Email message',
        ]);

        $timezone = config('recruitment.timezone');
        $scheduledAt = Carbon::createFromFormat('Y-m-d\TH:i', $validated['scheduled_at'], $timezone);

        if (! $scheduledAt || $scheduledAt->isPast()) {
            return back()->withErrors(['scheduled_at' => 'Choose an interview time in the future.'])->withInput();
        }

        $interview = $application->interviews()->first() ?? new Interview;
        $rescheduleToken = $interview->reschedule_token ?: bin2hex(random_bytes(32));
        $interview->fill([
            'created_by' => $request->user()->id,
            'scheduled_at' => $scheduledAt->utc(),
            'duration_minutes' => $validated['duration_minutes'],
            'mode' => $validated['mode'],
            'location' => trim($validated['location']),
            'instructions' => $validated['instructions'] ?? null,
            'email_subject' => trim($validated['email_subject']),
            'email_message' => trim($validated['email_message']),
            'status' => $validated['action'] === 'send' ? 'scheduled' : ($interview->status ?? 'scheduled'),
            'reschedule_token_hash' => $interview->reschedule_token_hash ?: hash('sha256', $rescheduleToken),
            'reschedule_token' => $rescheduleToken,
            'reschedule_reason' => $interview->reschedule_reason,
            'reschedule_requested_at' => $interview->reschedule_requested_at,
        ]);
        $application->interviews()->save($interview);

        AuditLog::record('interview.details_saved', $interview, [
            'application_reference' => $application->reference,
            'action' => $validated['action'],
        ]);

        if ($validated['action'] === 'save') {
            return redirect()->route('admin.interviews.index')
                ->with('success', 'Interview details saved as a draft. The invitation has not been sent.');
        }

        if ($application->status !== ApplicationStatus::Interview->value) {
            $transitioner->change($application, ApplicationStatus::Interview, $request->user(), 'Interview scheduled.');
        } else {
            $invitations->send($interview);
        }

        return redirect()->route('admin.interviews.index')
            ->with('success', 'Interview invitation queued for '.$application->full_name.'.');
    }
}
