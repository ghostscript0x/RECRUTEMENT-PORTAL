@php($data = $draft['data'] ?? [])
@php($states = \App\Models\State::orderBy('name')->pluck('name', 'id')->all())
@php($originStateId = old('state_of_origin_id', $data['state_of_origin_id'] ?? null))
@php($residenceStateId = old('residence_state_id', $data['residence_state_id'] ?? null))
@php($originLgas = $originStateId ? \App\Models\LocalGovernment::where('state_id', $originStateId)->orderBy('name')->pluck('name', 'id')->all() : [])
@php($residenceLgas = $residenceStateId ? \App\Models\LocalGovernment::where('state_id', $residenceStateId)->orderBy('name')->pluck('name', 'id')->all() : [])

<p class="mb-4 text-sm text-slate-500">Fields marked with <span class="text-rose-600">*</span> are required.</p>

<div class="grid gap-x-4 sm:grid-cols-3">
    <x-input name="first_name" label="First Name" required :value="$data['first_name'] ?? ''" autocomplete="given-name" />
    <x-input name="middle_name" label="Middle Name" :value="$data['middle_name'] ?? ''" />
    <x-input name="last_name" label="Last Name" required :value="$data['last_name'] ?? ''" autocomplete="family-name" />
</div>

<div class="grid gap-x-4 sm:grid-cols-2">
    <x-input name="email" label="Email Address" type="email" required :value="$data['email'] ?? ''" autocomplete="email" />
    <x-input name="phone" label="Phone Number" type="tel" required :value="$data['phone'] ?? ''" autocomplete="tel" />
</div>

<div class="grid gap-x-4 sm:grid-cols-2">
    <x-input name="date_of_birth" label="Date of Birth" type="date" required :value="$data['date_of_birth'] ?? ''" />
    <x-select name="gender" label="Gender / Sex" required placeholder="Select gender"
              :options="['male' => 'Male', 'female' => 'Female']" :value="$data['gender'] ?? ''" />
</div>

<div class="grid gap-x-4 sm:grid-cols-2">
    <x-select name="state_of_origin_id" label="State of Origin" :options="$states" :value="$originStateId"
              placeholder="Select state" data-lga-source="origin_lga_id" />
    <x-select name="origin_lga_id" label="Local Government Area" :options="$originLgas"
              :value="old('origin_lga_id', $data['origin_lga_id'] ?? '')" placeholder="Select LGA" />
</div>

<div class="grid gap-x-4 sm:grid-cols-2">
    <x-select name="residence_state_id" label="Current State of Residence" required :options="$states" :value="$residenceStateId"
              placeholder="Select state" data-lga-source="residence_lga_id" />
    <x-select name="residence_lga_id" label="Current LGA" :options="$residenceLgas"
              :value="old('residence_lga_id', $data['residence_lga_id'] ?? '')" placeholder="Select LGA" />
</div>

<x-textarea name="residential_address" label="Residential Address" required rows="3"
            :value="$data['residential_address'] ?? ''" placeholder="House number, street, city" />
