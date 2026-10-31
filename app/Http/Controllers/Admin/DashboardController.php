<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\Interview;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $positionId = $request->integer('position_id') ?: null;
        $reviewerId = $request->user()->role?->name === RoleEnum::Reviewer->value
            ? $request->user()->id
            : null;
        $canManageInterviews = $request->user()->hasAbility('interviews.manage');
        $canViewEmailDelivery = $request->user()->hasAbility('templates.manage');

        $counts = Application::query()
            ->visibleTo($request->user())
            ->when($positionId, fn ($query) => $query->where('position_id', $positionId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $counts->sum(),
            'new' => (int) $counts->get(ApplicationStatus::Submitted->value, 0),
            'review' => (int) $counts->get(ApplicationStatus::UnderReview->value, 0),
            'shortlisted' => (int) $counts->get(ApplicationStatus::Shortlisted->value, 0),
            'interview' => (int) $counts->get(ApplicationStatus::Interview->value, 0),
            'selected' => (int) $counts->get(ApplicationStatus::Selected->value, 0),
            'rejected' => (int) $counts->get(ApplicationStatus::Rejected->value, 0),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'counts' => $counts,
            'positions' => Position::withCount([
                'applications' => fn ($query) => $query->when(
                    $reviewerId,
                    fn ($query) => $query->where('assigned_reviewer_id', $reviewerId),
                ),
            ])->orderByDesc('applications_count')->get(),
            'recent' => Application::visibleTo($request->user())
                ->with(['position:id,title', 'residenceState:id,name'])
                ->when($positionId, fn ($query) => $query->where('position_id', $positionId))
                ->latest('submitted_at')
                ->limit(8)
                ->get(),
            'positionId' => $positionId,
            'statuses' => ApplicationStatus::cases(),
            'failedEmailCount' => $canViewEmailDelivery
                ? EmailLog::query()->where('status', 'failed')->count()
                : null,
            'upcomingInterviews' => $canManageInterviews
                ? Interview::query()
                    ->whereIn('status', ['scheduled', 'reschedule_requested'])
                    ->where('scheduled_at', '>=', now())
                    ->whereHas('application', fn ($query) => $query
                        ->where('status', ApplicationStatus::Interview->value)
                        ->when($positionId, fn ($query) => $query->where('position_id', $positionId)))
                    ->with([
                        'application:id,reference,first_name,middle_name,last_name,position_id',
                        'application.position:id,title',
                    ])
                    ->orderBy('scheduled_at')
                    ->limit(5)
                    ->get()
                : collect(),
        ]);
    }
}
