@extends('layouts.admin')

@section('title', 'Administrators')

@section('content')
    @php($fieldClass = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100')
    <div class="mx-auto max-w-7xl space-y-5">
        <header>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-orange">Secure access</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Administrators & roles</h1>
            <p class="mt-1 text-sm text-slate-600">Manage the people who can access recruitment information.</p>
        </header>

        <section class="grid gap-3 lg:grid-cols-3">
            @foreach($roles as $role)
                @php($permissions = $rolePermissions[$role->name] ?? [])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-brand-orange">Access role</p>
                    <h2 class="mt-1 font-bold text-slate-900">{{ $role->label }}</h2>
                    <ul class="mt-4 space-y-2">
                        @forelse($permissions as $permission)
                            <li class="flex items-start gap-2 text-xs leading-5 text-slate-600">
                                <span class="mt-0.5 font-bold text-emerald-700" aria-hidden="true">✓</span>
                                {{ $permission === '*' ? 'Full system access' : \Illuminate\Support\Str::headline(str_replace('.', ' ', $permission)) }}
                            </li>
                        @empty
                            <li class="text-xs text-slate-500">No permissions configured.</li>
                        @endforelse
                    </ul>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-bold text-slate-900">Administrator accounts</h2>
                <p class="mt-1 text-sm text-slate-500">{{ number_format($users->count()) }} {{ \Illuminate\Support\Str::plural('account', $users->count()) }} · Role changes take effect immediately.</p>
            </div>
            @if($users->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-slate-500">No administrator accounts found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Administrator</th>
                                <th scope="col" class="px-5 py-3">Access role</th>
                                <th scope="col" class="px-5 py-3">Added</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Manage access</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $user)
                                <tr class="align-middle hover:bg-slate-50/80">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-semibold text-slate-900">{{ $user->name }} @if($user->is(auth()->user()))<span class="ml-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-brand-blue">You</span>@endif</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $user->email }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $user->role?->label ?? 'No role assigned' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $user->created_at?->format('j M Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        @if($user->is(auth()->user()))
                                            <p class="text-right text-xs text-slate-500">Your own role cannot be changed here.</p>
                                        @else
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex gap-1.5">
                                                    @csrf
                                                    @method('PUT')
                                                    <label for="role-{{ $user->id }}" class="sr-only">Role for {{ $user->name }}</label>
                                                    <select id="role-{{ $user->id }}" name="role_id" class="max-w-52 rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs text-slate-700">
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs font-bold text-brand-blue hover:border-brand-blue">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Remove this administrator account?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Remove</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="max-w-2xl">
                <h2 class="font-bold text-slate-900">Add an administrator</h2>
                <p class="mt-1 text-sm text-slate-500">Create a secure account for a colleague. Use a unique email and a password of at least 10 characters.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Full name <span class="text-brand-orange">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="150" autocomplete="name" class="{{ $fieldClass }}">
                    @error('name')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email address <span class="text-brand-orange">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" class="{{ $fieldClass }}">
                    @error('email')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="role_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Access role <span class="text-brand-orange">*</span></label>
                    <select id="role_id" name="role_id" required class="{{ $fieldClass }}">
                        <option value="">Choose a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->label }}</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Temporary password <span class="text-brand-orange">*</span></label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required minlength="10" autocomplete="new-password" class="{{ $fieldClass }} pe-12">
                        <button type="button" class="absolute inset-y-0 right-0 inline-flex items-center justify-center rounded-r-lg px-3 text-slate-500 transition hover:text-brand-blue focus-visible:text-brand-blue"
                                aria-label="Show password" aria-controls="password" aria-pressed="false" data-password-toggle>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" data-password-show-icon>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.25 9.75-6.25S21.75 12 21.75 12 18.25 18.25 12 18.25 2.25 12 2.25 12Z"/>
                                <circle cx="12" cy="12" r="2.5"/>
                            </svg>
                            <svg class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" data-password-hide-icon>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.59a2 2 0 0 0 2.83 2.83"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.24A10.7 10.7 0 0 1 12 5c6.25 0 9.75 7 9.75 7a15.7 15.7 0 0 1-3.02 3.8M6.23 6.24C3.62 7.98 2.25 12 2.25 12s3.5 7 9.75 7c.83 0 1.62-.13 2.36-.36"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2 sm:flex sm:justify-end">
                    <button type="submit" class="brand-button w-full rounded-lg px-5 py-2.5 text-sm font-bold sm:w-auto">Create administrator</button>
                </div>
            </form>
        </section>
    </div>
@endsection
