<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Interview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InterviewRescheduleController extends Controller
{
    public function show(string $token): View
    {
        $interview = $this->findInterview($token);

        return view('interview-reschedule.show', [
            'interview' => $interview->load('application.position'),
            'timezone' => config('recruitment.timezone'),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $interview = $this->findInterview($token);

        if ($interview->status === 'reschedule_requested') {
            return back()->withErrors(['reason' => 'Your reschedule request has already been received. The recruitment team will contact you.']);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], attributes: ['reason' => 'Reason for rescheduling']);

        $interview->update([
            'status' => 'reschedule_requested',
            'reschedule_reason' => $validated['reason'],
            'reschedule_requested_at' => now(),
        ]);

        AuditLog::record('interview.reschedule_requested', $interview, [
            'application_reference' => $interview->application->reference,
        ]);

        return back()->with('success', 'Your request was sent. The recruitment team will contact you to agree on a new time.');
    }

    private function findInterview(string $token): Interview
    {
        $interview = Interview::query()
            ->where('reschedule_token_hash', hash('sha256', $token))
            ->whereIn('status', ['scheduled', 'reschedule_requested'])
            ->whereHas('application', fn ($query) => $query->where('status', 'interview'))
            ->firstOrFail();

        abort_if($interview->scheduled_at?->isPast(), 404);

        return $interview;
    }
}
