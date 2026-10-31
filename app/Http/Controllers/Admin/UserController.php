<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\Role as RoleEnum;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('role')->orderBy('name')->get(),
            'roles' => Role::orderBy('id')->get(),
            'rolePermissions' => collect(RoleEnum::cases())->mapWithKeys(
                fn (RoleEnum $role) => [$role->value => $role->abilities()],
            ),
        ]);
    }

    /** Administrators are created by an existing administrator — there is no public registration. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ], attributes: ['role_id' => 'Role']);

        User::create($validated + ['password' => Hash::make($validated['password'])]);

        AuditLog::record('user.created', null, ['email' => $validated['email'], 'role_id' => $validated['role_id']]);

        return back()->with('success', 'Administrator account created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ], attributes: ['role_id' => 'Role']);

        if ($user->is($request->user())) {
            return back()->withErrors(['role_id' => 'You cannot change your own role.']);
        }

        $user->update($validated);

        AuditLog::record('user.role_changed', $user, ['role_id' => $validated['role_id']]);

        return back()->with('success', 'Role updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot remove your own account.']);
        }

        $user->delete();

        AuditLog::record('user.deleted', $user, ['email' => $user->email]);

        return back()->with('success', 'Administrator removed.');
    }
}
