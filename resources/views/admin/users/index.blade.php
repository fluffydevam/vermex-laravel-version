@extends('layouts.app')

@section('title', 'Users')

@php
    use App\Enums\UserRole;
    use App\Enums\UserStatus;

    $roleOptions = ['' => 'All roles'];
    foreach ($roles as $case) {
        $roleOptions[$case->value] = $case->value;
    }

    $statusOptions = ['' => 'All statuses'];
    foreach ($statuses as $case) {
        $statusOptions[$case->value] = ucfirst($case->value);
    }

    $roleVariant = fn (UserRole $role) => match ($role) {
        UserRole::Admin => 'danger',
        UserRole::BillingOfficer => 'info',
        UserRole::FieldTechnician => 'success',
        UserRole::ChemicalCustodian => 'warning',
    };
@endphp

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
    <div
        x-data="{
            resetOpen: false,
            resetAction: '',
            resetName: '',
            openReset(action, name) {
                this.resetAction = action;
                this.resetName = name;
                this.resetOpen = true;
            }
        }"
    >
        <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-lg font-bold tracking-tight text-ink">Users</h1>
                <p class="mt-1 text-xs text-ink-muted">Create accounts, assign roles, and control access.</p>
            </div>
            <x-ui.button variant="primary" :href="route('admin.users.create')">New user</x-ui.button>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Total Accounts" :value="$stats['total']" variant="brand" />
            <x-ui.stat-card label="Active" :value="$stats['active']" variant="success" />
            <x-ui.stat-card label="Administrators" :value="$stats['admins']" variant="danger" />
            <x-ui.stat-card label="Field Technicians" :value="$stats['technicians']" variant="info" />
        </div>

        <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card">
            <form method="GET" action="{{ route('admin.users') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <x-ui.input label="Search" name="search" :value="$search" placeholder="Name, username, or email…" />
                </div>
                <x-ui.select label="Role" name="role" :value="$role" :options="$roleOptions" />
                <x-ui.select label="Status" name="status" :value="$status" :options="$statusOptions" />
                <div class="flex gap-2">
                    <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.users')">Reset</x-ui.button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-border bg-surface-muted">
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">User</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contact</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Role</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                            <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Last login</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($users as $account)
                            <tr class="transition-colors hover:bg-brand-50/40">
                                <td class="px-4 py-3 text-xs">
                                    <p class="font-medium text-ink">{{ $account->full_name ?: '—' }}</p>
                                    <p class="mt-0.5 font-mono text-[10px] text-ink-muted">@ {{ $account->username }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <p class="text-ink/90">{{ $account->email }}</p>
                                    <p class="mt-0.5 text-[10px] text-ink-muted">{{ $account->phone ?: 'No phone' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <x-ui.badge :variant="$roleVariant($account->role)">{{ $account->role->value }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <x-ui.badge :variant="$account->status === UserStatus::Active ? 'success' : 'default'">
                                        {{ ucfirst($account->status->value) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">
                                    {{ $account->last_login_at?->format('M d, Y H:i') ?? 'Never' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <x-ui.button variant="ghost" size="sm" :href="route('admin.users.edit', $account)">Edit</x-ui.button>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.toggle-status', $account) }}"
                                            onsubmit="return confirm('Change this account status?')"
                                        >
                                            @csrf
                                            <x-ui.button
                                                :variant="$account->status === UserStatus::Active ? 'outline' : 'success'"
                                                size="sm"
                                                type="submit"
                                                :disabled="$account->id === auth()->id()"
                                            >
                                                {{ $account->status === UserStatus::Active ? 'Disable' : 'Enable' }}
                                            </x-ui.button>
                                        </form>

                                        <x-ui.button
                                            variant="ghost"
                                            size="sm"
                                            type="button"
                                            data-action="{{ route('admin.users.reset-password', $account) }}"
                                            data-name="{{ $account->full_name }}"
                                            @click="openReset($el.dataset.action, $el.dataset.name)"
                                        >
                                            Reset password
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-2">
                                    <x-ui.empty-state title="No users in this view" message="Adjust the filters or create a new account." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <x-ui.pagination :paginator="$users" />
        </div>

        <div
            x-show="resetOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reset-modal-title"
        >
            <div class="absolute inset-0 bg-black/50" @click="resetOpen = false"></div>

            <div class="relative w-full max-w-md overflow-hidden rounded-md border border-border bg-surface shadow-card">
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <h2 id="reset-modal-title" class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Reset password</h2>
                    <button type="button" @click="resetOpen = false" class="text-ink-muted transition-colors hover:text-ink" aria-label="Close">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <form method="POST" :action="resetAction" class="space-y-4 p-5">
                    @csrf
                    <p class="text-xs text-ink-muted">
                        Set a new password for <span class="font-semibold text-ink" x-text="resetName"></span>.
                    </p>

                    <x-ui.input label="New password" name="new_password" type="password" required autocomplete="new-password" helper="Minimum 8 characters." />

                    <div class="flex justify-end gap-2 border-t border-border pt-4">
                        <x-ui.button variant="ghost" type="button" @click="resetOpen = false">Cancel</x-ui.button>
                        <x-ui.button variant="primary" type="submit">Reset password</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
