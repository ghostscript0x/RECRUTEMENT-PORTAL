<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademySetting;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => AcademySetting::query()->find(1) ?? new AcademySetting([
                'organization_name' => config('recruitment.organization_name'),
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:150'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'support_phone' => ['nullable', 'string', 'max:32'],
        ], attributes: [
            'organization_name' => 'Academy name',
            'support_email' => 'Support email',
            'support_phone' => 'Support phone',
        ]);

        $settings = AcademySetting::query()->find(1) ?? new AcademySetting;
        $settings->id = 1;
        $settings->fill($validated);
        $settings->updated_by = $request->user()->id;
        $settings->save();

        AuditLog::record('academy.settings_updated', $settings, [
            'organization_name' => $settings->organization_name,
        ]);

        return back()->with('success', 'Academy settings saved.');
    }
}
