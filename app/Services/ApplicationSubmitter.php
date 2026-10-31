<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationSkill;
use App\Models\ApplicationStatusHistory;
use App\Models\AuditLog;
use App\Models\Position;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Turns a completed wizard draft into a submitted application inside a single
 * transaction: reference number, skills, documents, initial status history and
 * audit entry, then queues the confirmation email.
 */
class ApplicationSubmitter
{
    public function submit(Position $position, array $data, array $skills, array $files, string $draftId): Application
    {
        $normalizedEmail = strtolower(trim($data['email']));

        try {
            $application = DB::transaction(function () use ($position, $data, $skills, $files, $draftId, $normalizedEmail) {
                $lockedPosition = Position::query()->lockForUpdate()->findOrFail($position->id);

                if (! $lockedPosition->isAccepting()) {
                    throw ValidationException::withMessages([
                        'position' => 'This position is no longer accepting applications.',
                    ]);
                }

                if (Application::query()
                    ->where('position_id', $lockedPosition->id)
                    ->where('email', $normalizedEmail)
                    ->exists()) {
                    throw $this->duplicateApplicationException();
                }

                $application = Application::create([
                    ...collect($data)->except(['position_id', 'skills'])->all(),
                    'reference' => ReferenceGenerator::generate(),
                    'position_id' => $lockedPosition->id,
                    'email' => $normalizedEmail,
                    'has_leadership' => ($data['has_leadership'] ?? 'no') === 'yes',
                    'graduation_year' => $data['graduation_year'] ?? null,
                    'status' => ApplicationStatus::Submitted->value,
                    'submitted_at' => now(),
                ]);

                ApplicationSkill::upsert(
                    collect($skills)->unique()->map(fn ($name) => [
                        'application_id' => $application->id,
                        'name' => trim($name),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all(),
                    ['application_id', 'name'],
                    ['updated_at'],
                );

                $this->persistDocuments($application, $files, $draftId);

                ApplicationStatusHistory::create([
                    'application_id' => $application->id,
                    'from_status' => null,
                    'to_status' => ApplicationStatus::Submitted->value,
                    'created_at' => now(),
                ]);

                AuditLog::record('application.submitted', $application, [
                    'position' => $lockedPosition->title,
                    'email' => $application->email,
                ]);

                return $application;
            });
        } catch (QueryException $exception) {
            $duplicateExists = Application::query()
                ->where('position_id', $position->id)
                ->where('email', $normalizedEmail)
                ->exists();

            if (! $duplicateExists) {
                throw $exception;
            }

            throw $this->duplicateApplicationException();
        }

        NotificationDispatcher::send($application, ApplicationStatus::Submitted->emailTemplateKey());

        return $application;
    }

    private function duplicateApplicationException(): ValidationException
    {
        return ValidationException::withMessages([
            'email' => 'An application for this position has already been submitted with this email address.',
        ]);
    }

    /** Move wizard-staged uploads into the application's private directory. */
    private function persistDocuments(Application $application, array $files, string $draftId): void
    {
        $disk = Storage::disk('local');

        foreach ($files as $type => $path) {
            if (! $disk->exists($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $target = sprintf('applications/%s/%s-%s.%s', $application->reference, $type, Str::lower(Str::random(6)), $extension);

            $disk->move($path, $target);

            ApplicationDocument::create([
                'application_id' => $application->id,
                'type' => $type,
                'path' => $target,
                'original_name' => basename($path),
                'mime_type' => $disk->mimeType($target) ?? 'application/octet-stream',
                'size' => (int) $disk->size($target),
            ]);
        }

        $disk->deleteDirectory("applications/pending/{$draftId}");
    }
}
