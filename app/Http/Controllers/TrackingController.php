<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\LocalGovernment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public tracking portal: a reference number and email address must BOTH match
 * before any application data is returned, and failures are deliberately
 * indistinguishable from one another.
 */
class TrackingController extends Controller
{
    public function show(Request $request): View
    {
        return view('tracking.show', [
            'reference' => $request->query('reference', ''),
            'application' => null,
            'notFound' => false,
        ]);
    }

    public function apiStateLgas(Request $request, $stateId): JsonResponse
    {
        $stateId = (int) $stateId;

        $lgas = LocalGovernment::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($lgas);
    }

    public function track(Request $request): RedirectResponse|View
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:150'],
        ], [], ['reference' => 'Application Reference Number', 'email' => 'Email Address']);

        $reference = strtoupper(trim($validated['reference']));
        $email = strtolower(trim($validated['email']));

        // Only public-safe columns: internal notes are never selected here.
        $application = Application::with([
            'position:id,title,department',
            'residenceState:id,name',
            'statusHistory' => fn ($query) => $query->select(['application_id', 'from_status', 'to_status', 'created_at']),
        ])
            ->where('reference', $reference)
            ->where('email', $email)
            ->first();

        if (! $application) {
            return view('tracking.show', [
                'reference' => $validated['reference'],
                'application' => null,
                'notFound' => true,
            ]);
        }

        return view('tracking.show', [
            'reference' => $application->reference,
            'application' => $application,
            'notFound' => false,
        ]);
    }
}
