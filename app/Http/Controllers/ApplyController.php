<?php

namespace App\Http\Controllers;

use App\Models\LocalGovernment;
use App\Models\Position;
use App\Models\State;
use App\Services\ApplicationSubmitter;
use App\Services\ApplicationWizard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Multi-step application form. All step definitions, rules and labels live in
 * ApplicationWizard; this controller only orchestrates navigation, draft
 * storage and final submission.
 */
class ApplyController extends Controller
{
    public function __construct(private readonly ApplicationSubmitter $submitter) {}

    /** Begin (or resume) an application for one position. */
    public function start(Request $request, Position $position): RedirectResponse
    {
        abort_unless($position->isAccepting(), 404);

        $draft = ApplicationWizard::draft($request, $position);
        $draft += ['completed' => [], 'files' => [], 'file_names' => []];
        $draft['draft_id'] ??= (string) Str::uuid();
        $draft['data'] = array_merge(['position_id' => $position->id], $draft['data'] ?? []);
        $request->session()->put(ApplicationWizard::draftKey($position), $draft);

        if ($missing = $this->missingSteps($position, $request)) {
            return redirect()->route('apply.step', [$position, min($missing)]);
        }

        return redirect()->route('apply.review', $position);
    }

    public function showStep(Request $request, Position $position, int $step): View|RedirectResponse
    {
        $allowed = $this->furthestAllowed($position, $request);

        if ($step > $allowed) {
            return redirect()->route('apply.step', [$position, $allowed]);
        }

        return view('apply.wizard', ApplicationWizard::viewData($position, $step) + [
            'draft' => ApplicationWizard::draft($request, $position),
        ]);
    }

    public function storeStep(Request $request, Position $position, int $step): RedirectResponse
    {
        $allowed = $this->furthestAllowed($position, $request);

        if ($step > $allowed) {
            return redirect()->route('apply.step', [$position, $allowed]);
        }

        $validated = $request->validate(
            ApplicationWizard::rules($step, $request, $position),
            attributes: ApplicationWizard::LABELS,
        );

        if ($step === 1) {
            /** @var Position $chosen */
            $chosen = Position::findOrFail($validated['position_id']);

            if (! $chosen->isAccepting()) {
                return back()->withErrors(['position_id' => 'This position is no longer accepting applications.']);
            }

            if ($chosen->isNot($position)) {
                return redirect()->route('apply.start', $chosen);
            }
        }

        if ($step === 7) {
            $this->storeDocuments($request, $position, $validated);
        }

        if ($step === 3) {
            if ($validated['highest_qualification'] === 'Other') {
                $validated['highest_qualification'] = $validated['highest_qualification_other'];
            }

            unset($validated['highest_qualification_other']);
        }

        if ($step === 5 && $validated['has_leadership'] === 'no') {
            $validated['leadership_organization'] = null;
            $validated['leadership_position'] = null;
            $validated['leadership_duration'] = null;
            $validated['leadership_description'] = null;
        }

        ApplicationWizard::store($request, $position, $validated);

        $draft = ApplicationWizard::draft($request, $position);
        $draft['completed'] = array_values(array_unique([...($draft['completed'] ?? []), $step]));
        $request->session()->put(ApplicationWizard::draftKey($position), $draft);

        return $step < ApplicationWizard::COUNT
            ? redirect()->route('apply.step', [$position, $step + 1])
            : redirect()->route('apply.review', $position);
    }

    public function review(Request $request, Position $position): View|RedirectResponse
    {
        if ($missing = $this->missingSteps($position, $request)) {
            return redirect()->route('apply.step', [$position, min($missing)]);
        }

        $draft = ApplicationWizard::draft($request, $position);
        $data = $draft['data'] ?? [];
        $stateIds = collect([
            $data['state_of_origin_id'] ?? null,
            $data['residence_state_id'] ?? null,
        ])->filter()->unique();
        $localGovernmentIds = collect([
            $data['origin_lga_id'] ?? null,
            $data['residence_lga_id'] ?? null,
        ])->filter()->unique();

        return view('apply.review', [
            'position' => $position,
            'draft' => $draft,
            'stateNames' => $stateIds->isNotEmpty()
                ? State::whereKey($stateIds->all())->pluck('name', 'id')
                : collect(),
            'localGovernmentNames' => $localGovernmentIds->isNotEmpty()
                ? LocalGovernment::whereKey($localGovernmentIds->all())->pluck('name', 'id')
                : collect(),
        ]);
    }

    public function submit(Request $request, Position $position): RedirectResponse
    {
        $request->validate(['confirm' => ['accepted']], attributes: ['confirm' => 'confirmation']);

        if (! $position->isAccepting()) {
            return back()->withErrors([
                'position' => 'This position is no longer accepting applications.',
            ]);
        }

        if ($missing = $this->missingSteps($position, $request)) {
            return redirect()->route('apply.step', [$position, min($missing)]);
        }

        $draft = ApplicationWizard::draft($request, $position);
        $data = $draft['data'] + ['position_id' => $position->id];

        $application = $this->submitter->submit(
            $position,
            $data,
            $data['skills'] ?? [],
            $draft['files'] ?? [],
            $draft['draft_id'],
        );

        $request->session()->forget(ApplicationWizard::draftKey($position));

        return redirect()->route('apply.success')
            ->with('reference', $application->reference)
            ->with('email', $application->email);
    }

    public function success(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->pull('reference');

        if (! $reference) {
            return redirect()->route('home');
        }

        return view('apply.success', ['reference' => $reference, 'email' => $request->session()->pull('email')]);
    }

    /** Stage uploads in the draft's pending folder until the application is submitted. */
    private function storeDocuments(Request $request, Position $position, array $validated): void
    {
        $draft = ApplicationWizard::draft($request, $position);
        $draftId = $draft['draft_id'];
        $uploads = [];

        foreach ($request->file('documents', []) as $key => $file) {
            if (! $file->isValid()) {
                continue;
            }

            $extension = strtolower($file->extension() ?: 'bin');
            $uploads[$key] = $file->storeAs("applications/pending/{$draftId}", $key.'.'.$extension, 'local');
            $draft['file_names'][$key] = $file->getClientOriginalName();
        }

        $draft['files'] = array_merge($draft['files'] ?? [], $uploads);
        $request->session()->put(ApplicationWizard::draftKey($position), $draft);
    }

    private function furthestAllowed(Position $position, Request $request): int
    {
        $completed = ApplicationWizard::draft($request, $position)['completed'] ?? [];

        return $completed ? max($completed) + 1 : 1;
    }

    /** @return list<int> */
    private function missingSteps(Position $position, Request $request): array
    {
        $completed = ApplicationWizard::draft($request, $position)['completed'] ?? [];

        return array_values(array_diff(range(1, ApplicationWizard::COUNT), $completed));
    }
}
