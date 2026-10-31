<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(): View
    {
        return view('admin.positions.index', [
            'positions' => Position::withCount('applications')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Position(['status' => 'draft']));
    }

    public function edit(Position $position): View
    {
        return $this->form($position);
    }

    public function store(Request $request): RedirectResponse
    {
        $position = Position::create($this->validated($request));

        AuditLog::record('position.created', $position, ['title' => $position->title]);

        return redirect()->route('admin.positions.edit', $position)->with('success', 'Position created.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $position->update($this->validated($request, $position));

        AuditLog::record('position.updated', $position, ['title' => $position->title]);

        return redirect()->route('admin.positions.edit', $position)->with('success', 'Position updated.');
    }

    /** Publish, close or archive without touching submitted applications. */
    public function updateStatus(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Position::STATUSES))],
        ]);

        $position->update(['status' => $validated['status']]);

        AuditLog::record('position.status_changed', $position, ['status' => $validated['status']]);

        return back()->with('success', 'Position marked as '.Position::STATUSES[$validated['status']].'.');
    }

    private function form(Position $position): View
    {
        return view('admin.positions.form', [
            'position' => $position,
            'documentOptions' => config('recruitment.default_documents'),
            'statuses' => Position::STATUSES,
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Position $position = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'department' => ['nullable', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'status' => ['required', 'in:'.implode(',', array_keys(Position::STATUSES))],
            'document_usage' => ['nullable', 'array'],
            'document_usage.*' => ['nullable', 'in:required,optional'],
        ], attributes: [
            'title' => 'Position Title', 'opens_at' => 'Opening Date', 'closes_at' => 'Closing Date',
            'description' => 'Description', 'requirements' => 'Requirements',
        ]);

        $labels = collect(config('recruitment.default_documents'))->keyBy('key');
        $usage = $request->input('document_usage', []);

        $validated['required_documents'] = collect($usage)
            ->filter(fn ($choice) => $choice === 'required')
            ->map(fn ($choice, $key) => ['key' => $key, 'label' => $labels[$key]['label'] ?? ucfirst($key)])
            ->values()->all();

        $validated['optional_documents'] = collect($usage)
            ->filter(fn ($choice) => $choice === 'optional')
            ->map(fn ($choice, $key) => ['key' => $key, 'label' => $labels[$key]['label'] ?? ucfirst($key)])
            ->values()->all();

        unset($validated['document_usage']);

        return $validated;
    }
}
