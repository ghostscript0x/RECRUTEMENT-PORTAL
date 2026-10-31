<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * The one place application statuses change. Every transition records who did
 * it, the previous status, the timestamp, an optional internal note, an audit
 * entry and the matching applicant notification.
 */
class StatusTransitioner
{
    public function __construct(private InterviewInvitationDispatcher $interviewInvitations) {}

    public function change(
        Application $application,
        ApplicationStatus $to,
        ?User $user = null,
        ?string $note = null,
    ): Application {
        $from = ApplicationStatus::from($application->status);

        if ($from === $to) {
            return $application;
        }

        $application->update(['status' => $to->value]);

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'user_id' => ($user ?? Auth::user())?->id,
            'note' => $note,
            'created_at' => now(),
        ]);

        AuditLog::record('application.status_changed', $application, [
            'from' => $from->value,
            'to' => $to->value,
            'note' => $note,
        ]);

        if ($to === ApplicationStatus::Interview) {
            $this->interviewInvitations->send($application->interviews()->firstOrFail());
        } else {
            NotificationDispatcher::send($application, $to->emailTemplateKey());
        }

        return $application->refresh();
    }
}
