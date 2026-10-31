<?php

namespace App\Jobs;

use App\Mail\TemplateMail;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers one queued notification with retries and updates the email log so
 * administrators can see what was sent, what failed and why.
 */
class SendApplicationEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(public int $emailLogId) {}

    public function handle(): void
    {
        $log = EmailLog::find($this->emailLogId);

        if (! $log || $log->status === 'sent') {
            return;
        }

        $application = $log->application;

        if (! $application) {
            $log->update(['status' => 'failed', 'error' => 'Application no longer exists.']);

            return;
        }

        $log->update(['status' => 'sending', 'error' => null]);
        Mail::to($log->to_email)->send(new TemplateMail($log->template_key, $application, $log->context ?? []));

        $log->update(['status' => 'sent', 'error' => null]);
    }

    public function failed(?\Throwable $exception): void
    {
        EmailLog::find($this->emailLogId)?->update([
            'status' => 'failed',
            'error' => 'Email delivery failed after all retry attempts. Check application logs for details.',
        ]);
    }
}
