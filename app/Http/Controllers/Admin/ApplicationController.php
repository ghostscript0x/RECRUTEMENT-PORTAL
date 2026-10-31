<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\Role as RoleEnum;
use App\Exports\ApplicationExporter;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\AuditLog;
use App\Models\LocalGovernment;
use App\Models\Position;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use App\Services\ApplicationWizard;
use App\Services\StatusTransitioner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    /** Filters are read from the model scope, so the table, the export and the
     * dashboard counts all share exactly the same query. */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'sort' => ['nullable', 'in:submitted_at,reference,first_name,last_name,status'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);
        $sort = $validated['sort'] ?? 'submitted_at';
        $direction = $validated['direction'] ?? 'desc';

        $applications = Application::visibleTo($request->user())
            ->filtered($request)
            ->orderBy($sort, $direction)
            ->paginate(config('recruitment.per_page'))
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'positions' => Position::orderBy('title')->get(['id', 'title']),
            'states' => State::orderBy('name')->get(['id', 'name']),
            'localGovernments' => $request->filled('state_id')
                ? LocalGovernment::where('state_id', $request->input('state_id'))->orderBy('name')->get(['id', 'name'])
                : collect(),
            'statuses' => ApplicationStatus::cases(),
            'qualifications' => ApplicationWizard::qualifications(),
            'filters' => $request->only(['q', 'status', 'position_id', 'state_id', 'lga_id', 'qualification', 'gender', 'leadership', 'from', 'to']),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function show(Request $request, Application $application): View
    {
        $this->ensureCanView($request, $application);
        $application->load([
            'position', 'assignedReviewer', 'stateOfOrigin', 'originLga', 'residenceState', 'residenceLga',
            'skills', 'documents', 'interviews', 'notes.user', 'statusHistory.user',
        ]);

        return view('admin.applications.show', [
            'application' => $application,
            'statuses' => ApplicationStatus::cases(),
            'reviewers' => $request->user()->hasAbility('applications.assign')
                ? (Role::where('name', RoleEnum::Reviewer->value)->first()?->users()
                    ->orderBy('name')
                    ->get(['users.id', 'users.name', 'users.email']) ?? collect())
                : collect(),
        ]);
    }

    public function assignReviewer(Request $request, Application $application): RedirectResponse
    {
        $this->ensureCanView($request, $application);
        $validated = $request->validate([
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
        ], attributes: ['reviewer_id' => 'Application Reviewer']);

        $reviewerId = $validated['reviewer_id'] ?? null;

        if ($reviewerId !== null && ! User::whereKey($reviewerId)
            ->whereHas('role', fn ($query) => $query->where('name', RoleEnum::Reviewer->value))
            ->exists()) {
            return back()->withErrors([
                'reviewer_id' => 'Select an administrator with the Application Reviewer role.',
            ]);
        }

        $application->update(['assigned_reviewer_id' => $reviewerId]);
        AuditLog::record('application.reviewer_assigned', $application, [
            'reviewer_id' => $reviewerId,
        ]);

        return back()->with('success', $reviewerId === null
            ? 'Reviewer assignment removed.'
            : 'Application assigned to reviewer.');
    }

    public function updateStatus(Request $request, Application $application, StatusTransitioner $transitioner): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['status' => 'Status', 'note' => 'Internal Note']);

        $targetStatus = ApplicationStatus::from($validated['status']);

        if ($targetStatus === ApplicationStatus::Interview && $application->status !== $targetStatus->value) {
            $interview = $application->interviews()->first();

            if (! $interview?->isReadyForInvitation()) {
                if ($application->status === ApplicationStatus::Shortlisted->value) {
                    return redirect()->route('admin.interviews.edit', $application)
                        ->withErrors(['interview' => 'Set the interview time, contact details, and invitation message before moving this applicant to interview.']);
                }

                return back()->withErrors(['status' => 'Shortlist this applicant first, then prepare the interview invitation.']);
            }
        }

        $transitioner->change(
            $application,
            $targetStatus,
            $request->user(),
            ($validated['note'] ?? null) ?: null,
        );

        return back()->with('success', 'Status updated to "'.ApplicationStatus::from($validated['status'])->label().'".');
    }

    public function addNote(Request $request, Application $application): RedirectResponse
    {
        $this->ensureCanView($request, $application);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], attributes: ['body' => 'Note']);

        ApplicationNote::create([
            'application_id' => $application->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        AuditLog::record('application.note_added', $application);

        return back()->with('success', 'Internal note added.');
    }

    public function export(Request $request, ApplicationExporter $exporter): StreamedResponse
    {
        $request->validate(['format' => ['required', Rule::in(['csv', 'xlsx'])]]);

        return $exporter->download($request, $request->input('format'));
    }

    private function ensureCanView(Request $request, Application $application): void
    {
        if ($request->user()->role?->name === RoleEnum::Reviewer->value
            && $application->assigned_reviewer_id !== $request->user()->id) {
            abort(404);
        }
    }
}
