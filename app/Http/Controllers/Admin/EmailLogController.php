<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['queued', 'sending', 'sent', 'failed'])],
            'q' => ['nullable', 'string', 'max:150'],
        ]);
        $term = trim($validated['q'] ?? '');

        $logs = EmailLog::with('application:id,reference')
            ->when(isset($validated['status']), fn ($query) => $query->where('status', $validated['status']))
            ->when($term !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('to_email', 'like', "%{$term}%")
                ->orWhere('subject', 'like', "%{$term}%")
                ->orWhere('template_key', 'like', "%{$term}%")
            ))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.email-logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function retry(EmailLog $emailLog): RedirectResponse
    {
        if ($emailLog->status === 'sent') {
            return back()->with('emailStatus', 'This email has already been sent.');
        }

        if ($emailLog->status !== 'failed') {
            return back()->with('emailError', 'Only failed emails can be retried. Queued emails are already waiting for delivery.');
        }

        $emailLog = NotificationDispatcher::retry($emailLog);

        return back()->with('emailStatus', "A retry for {$emailLog->to_email} has been queued.");
    }
}
