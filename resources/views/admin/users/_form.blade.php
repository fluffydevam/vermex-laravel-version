@php
    $isEdit = isset($user);
    $roleOptions = collect($roles ?? \App\Enums\UserRole::cases())->mapWithKeys(fn ($role) => [$role->value => $role->value])->all();
    $statusOptions = collect($statuses ?? \App\Enums\UserStatus::cases())->mapWithKeys(fn ($status) => [$status->value => ucfirst($status->value)])->all();
@endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="space-y-5">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Identity</h2>

        <x-ui.input label="First name" name="first_name" :value="$user->first_name ?? null" required />
        <x-ui.input label="Last name" name="last_name" :value="$user->last_name ?? null" required />
        <x-ui.input label="Username" name="username" :value="$user->username ?? null" required helper="Letters, numbers, dashes, and underscores." />
        <x-ui.input label="Email" name="email" type="email" :value="$user->email ?? null" required />
        <x-ui.input label="Phone" name="phone" :value="$user->phone ?? null" />
    </div>

    <div class="space-y-5">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Access</h2>

        <x-ui.select label="Role" name="role" :value="$user->role ?? \App\Enums\UserRole::FieldTechnician" :placeholder="null" :options="$roleOptions" required />
        <x-ui.select label="Status" name="status" :value="$user->status ?? \App\Enums\UserStatus::Active" :placeholder="null" :options="$statusOptions" required />
        <x-ui.input label="Sector / region" name="sector_region" :value="$user->sector_region ?? null" placeholder="e.g. Davao City" />

        <x-ui.input
            label="Password"
            name="password"
            type="password"
            :required="! $isEdit"
            :helper="$isEdit ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.'"
            autocomplete="new-password"
        />

        <x-ui.alert type="info" title="Account safety">
            Administrators cannot disable their own account, and the last active administrator cannot be demoted or disabled.
        </x-ui.alert>
    </div>
</div>
