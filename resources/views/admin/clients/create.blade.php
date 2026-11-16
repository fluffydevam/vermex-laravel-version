@extends('layouts.app')

@section('title', 'Add Client')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Add New Client</h1>
            <p class="mt-1 text-xs text-ink-muted">Register a new client account.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.clients')">Back to list</x-ui.button>
    </div>

    <div class="max-w-3xl">
        @if ($errors->any())
            <div class="mb-6">
                <x-ui.alert type="error" title="Please review the form">
                    {{ $errors->first() }}
                </x-ui.alert>
            </div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-5">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Account</h2>

                        <x-ui.input label="Client / Company Name" name="client_name" required />
                        <x-ui.input label="First Name" name="first_name" required />
                        <x-ui.input label="Last Name" name="last_name" required />
                        <x-ui.input label="Phone Number" name="phone_number" type="tel" required />
                        <x-ui.input label="Email" name="email" type="email" />
                    </div>

                    <div class="space-y-5">
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Service address</h2>

                        <x-ui.select label="Client Type" name="client_type" required :placeholder="null" :options="[
                            'Residential' => 'Residential',
                            'Commercial' => 'Commercial',
                        ]" />
                        <x-ui.input label="Street Address" name="street_address" required />
                        <x-ui.input label="Barangay" name="barangay" required />
                        <x-ui.input label="City" name="city" value="Davao City" required />
                        <x-ui.select label="Status" name="status" required :placeholder="null" :options="[
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ]" />
                    </div>
                </div>

                <div class="flex gap-3 border-t border-border pt-6">
                    <x-ui.button variant="primary" type="submit">Register Client</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.clients')">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
