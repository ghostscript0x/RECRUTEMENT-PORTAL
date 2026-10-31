<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmailTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::query()->where('key', '!=', 'interview')->orderBy('name')->get(),
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        abort_unless($emailTemplate->key !== 'interview', 404);

        return view('admin.email-templates.edit', ['template' => $emailTemplate]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        abort_unless($emailTemplate->key !== 'interview', 404);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:20000'],
            'enabled' => ['nullable', 'boolean'],
        ], attributes: ['subject' => 'Subject Line', 'body' => 'Email Content']);

        $emailTemplate->update([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'enabled' => $request->boolean('enabled'),
        ]);

        AuditLog::record('email_template.updated', $emailTemplate, ['key' => $emailTemplate->key]);

        return back()->with('success', 'Template saved.');
    }
}
