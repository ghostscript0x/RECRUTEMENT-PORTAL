<?php

namespace App\Services;

use App\Jobs\SendApplicationEmail;
use App\Models\AuditLog;
use App\Models\EmailLog;
use App\Models\Interview;
use DomainException;
use Illuminate\Support\Carbon;

class InterviewInvitationDispatcher
{
    public function send(Interview $interview): EmailLog
    {
        if (! $interview->isReadyForInvitation()) {
            throw new DomainException('Complete the interview schedule and invitation before sending.');
        }

        $application = $interview->loadMissing('application.position')->application;
        $timezone = config('recruitment.timezone');
        $localTime = Carbon::instance($interview->scheduled_at)->setTimezone($timezone);
        $rescheduleUrl = route('interview-reschedule.show', ['token' => $interview->reschedule_token]);
        $subject = str_replace(["\r", "\n"], ' ', trim($interview->email_subject));
        $context = [
            'interview_date' => $localTime->format('l, j F Y'),
            'interview_time' => $localTime->format('g:i A'),
            'interview_timezone' => $timezone === 'Africa/Lagos' ? 'WAT (Africa/Lagos)' : $timezone,
            'interview_mode' => $interview->channelLabel(),
            'interview_location' => $interview->location,
            'interview_instructions' => (string) ($interview->instructions ?? ''),
            'interview_email_subject' => $subject,
            'interview_email_message' => trim($interview->email_message),
            'reschedule_url' => $rescheduleUrl,
        ];
        $emailLog = EmailLog::create([
            'application_id' => $application->id,
            'template_key' => 'interview',
            'to_email' => $application->email,
            'subject' => $subject,
            'status' => 'queued',
            'context' => $context,
            'created_at' => now(),
        ]);

        SendApplicationEmail::dispatch($emailLog->id);
        $interview->update(['invitation_sent_at' => now()]);

        AuditLog::record('interview.invitation_sent', $interview, [
            'application_reference' => $application->reference,
            'mode' => $interview->mode,
            'scheduled_at' => $interview->scheduled_at->toIso8601String(),
        ]);

        return $emailLog->refresh();
    }
}
