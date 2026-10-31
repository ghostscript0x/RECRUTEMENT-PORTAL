<?php

namespace App\Services;

use App\Jobs\SendApplicationEmail;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\EmailTemplate;

/**
 * Decides whether a notification should go out, records the delivery attempt
 * and queues it for delivery. Status changes only fire once, so an application
 * never gets duplicate notifications for the same event.
 */
class NotificationDispatcher
{
    public static function send(
        Application $application,
        string $templateKey,
        bool $force = false,
        array $context = [],
    ): ?EmailLog {
        $template = EmailTemplate::where('key', $templateKey)->first();

        if (! $template?->enabled) {
            return null;
        }

        $alreadySent = EmailLog::where('application_id', $application->id)
            ->where('template_key', $templateKey)
            ->whereIn('status', ['queued', 'sending', 'sent'])
            ->exists();

        if ($alreadySent && ! $force) {
            return null;
        }

        $log = EmailLog::create([
            'application_id' => $application->id,
            'template_key' => $templateKey,
            'to_email' => $application->email,
            'subject' => str_replace(
                array_map(fn (string $key): string => '{{'.$key.'}}', array_keys($context)),
                array_map(fn (mixed $value): string => strip_tags((string) $value), array_values($context)),
                $template->subject,
            ),
            'status' => 'queued',
            'context' => $context ?: null,
            'created_at' => now(),
        ]);

        SendApplicationEmail::dispatch($log->id);

        return $log->refresh();
    }

    public static function retry(EmailLog $log): EmailLog
    {
        if ($log->status !== 'failed') {
            return $log;
        }

        $log->update([
            'status' => 'queued',
            'error' => null,
        ]);

        SendApplicationEmail::dispatch($log->id);

        return $log->refresh();
    }
}
