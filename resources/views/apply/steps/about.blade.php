@php($data = $draft['data'] ?? [])

<x-textarea name="about" label="Tell us about yourself" required rows="9"
            :maxlength="(int) config('recruitment.about_max_length')"
            :value="$data['about'] ?? ''"
            placeholder="Introduce yourself, describe your experience and explain why you are suitable for this position." />

<p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600">
    There is no minimum length — but a clear, well-structured answer helps reviewers understand your fit for the role.
</p>
