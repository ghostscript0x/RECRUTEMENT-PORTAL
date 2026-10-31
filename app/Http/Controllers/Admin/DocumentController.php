<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Documents live in private storage; the only way to reach them is this
 * authorised route, which is gated to administrators.
 */
class DocumentController extends Controller
{
    public function show(Request $request, Application $application, ApplicationDocument $document): BinaryFileResponse
    {
        abort_unless($document->application_id === $application->id, 404);
        abort_unless(
            $request->user()->role?->name !== RoleEnum::Reviewer->value
                || $application->assigned_reviewer_id === $request->user()->id,
            404,
        );

        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->path), 404);
        abort_unless(in_array($document->mime_type, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true), 404);

        $response = response()->file($disk->path($document->path), [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($document->path),
        );

        return $response;
    }
}
