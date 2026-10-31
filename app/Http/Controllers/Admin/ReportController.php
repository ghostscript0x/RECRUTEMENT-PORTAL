<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
        ]);
        $filters = [
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
            'position_id' => $validated['position_id'] ?? '',
        ];
        $reviewerId = $request->user()->role?->name === RoleEnum::Reviewer->value
            ? $request->user()->id
            : null;
        $applyFilters = static function (Builder $query) use ($filters, $reviewerId): Builder {
            return $query
                ->when($reviewerId, fn ($query) => $query->where('assigned_reviewer_id', $reviewerId))
                ->when($filters['from'] !== '', fn ($query) => $query->whereDate('submitted_at', '>=', $filters['from']))
                ->when($filters['to'] !== '', fn ($query) => $query->whereDate('submitted_at', '<=', $filters['to']))
                ->when($filters['position_id'] !== '', fn ($query) => $query->where('position_id', $filters['position_id']));
        };

        return view('admin.reports.index', [
            'filters' => $filters,
            'positions' => Position::orderBy('title')->get(['id', 'title']),
            'total' => $applyFilters(Application::query())->count(),
            'byStatus' => $applyFilters(Application::query())
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'byPosition' => Position::query()
                ->withCount(['applications' => fn (Builder $query) => $applyFilters($query)])
                ->orderByDesc('applications_count')
                ->get(['id', 'title', 'department']),
            'byState' => $applyFilters(Application::query())
                ->join('states', 'applications.residence_state_id', '=', 'states.id')
                ->selectRaw('states.id, states.name, count(*) as total')
                ->groupBy('states.id', 'states.name')
                ->orderByDesc('total')
                ->get(),
            'byQualification' => $applyFilters(Application::query())
                ->selectRaw('highest_qualification as label, count(*) as total')
                ->groupBy('highest_qualification')
                ->orderByDesc('total')
                ->get(),
            'byGender' => $applyFilters(Application::query())
                ->selectRaw('gender as label, count(*) as total')
                ->groupBy('gender')
                ->orderByDesc('total')
                ->get(),
            'shortlisted' => (int) $applyFilters(Application::query())->where('status', 'shortlisted')->count(),
            'selected' => (int) $applyFilters(Application::query())->where('status', 'selected')->count(),
            'rejected' => (int) $applyFilters(Application::query())->where('status', 'rejected')->count(),
        ]);
    }
}
