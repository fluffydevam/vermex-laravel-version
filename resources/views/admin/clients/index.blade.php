@extends('layouts.app')

@section('title', 'Clients & Contracts')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Clients &amp; Contracts</h1>
            <p class="mt-1 text-xs text-ink-muted">Manage client accounts, contracts, and billing relationships.</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button variant="ghost" :href="route($indexRoute)">Refresh</x-ui.button>
            @can('create', App\Models\Client::class)
                <x-ui.button variant="primary" :href="route('admin.clients.create')">Add Client</x-ui.button>
            @endcan
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <x-ui.stat-card label="Total Clients" :value="$kpis['total']" />
        <x-ui.stat-card label="Active" :value="$kpis['active']" variant="success" />
        <x-ui.stat-card label="Inactive" :value="$kpis['inactive']" variant="warning" />
        <x-ui.stat-card label="Archived" :value="$kpis['archived']" variant="danger" />
        <x-ui.stat-card label="Commercial" :value="$kpis['commercial']" variant="info" />
        <x-ui.stat-card label="Residential" :value="$kpis['residential']" variant="brand" />
    </div>

    <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card">
        <form method="GET" action="{{ route($indexRoute) }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="lg:col-span-2">
                <x-ui.input label="Search" name="search" :value="$search" placeholder="Name, email, phone…" />
            </div>

            @if ($tab === 'contracts')
                <x-ui.select label="Contract status" name="status" :value="$filterStatus" :options="[
                    '' => 'All statuses',
                    'to_be_contracted' => 'To be contracted',
                    'active' => 'Active',
                    'expiring_soon' => 'Expiring soon',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                    'archived' => 'Archived',
                ]" />
            @else
                <x-ui.select label="Type" name="type" :value="$filterType" :options="[
                    '' => 'All types',
                    'Residential' => 'Residential',
                    'Commercial' => 'Commercial',
                ]" />
                <x-ui.select label="Status" name="status" :value="$filterStatus" :options="[
                    '' => 'All statuses',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'archived' => 'Archived',
                ]" />
            @endif

            <div class="flex gap-2">
                <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
                <x-ui.button variant="ghost" :href="route($indexRoute, ['tab' => $tab])">Reset</x-ui.button>
            </div>
        </form>
    </div>

    <div class="mb-6 flex flex-wrap gap-1">
        @foreach ([
            'clients' => 'Active Clients',
            'contracts' => 'Contracts',
            'archived' => 'Archived Clients',
        ] as $key => $label)
            <a
                href="{{ route($indexRoute, ['tab' => $key]) }}"
                class="rounded-sm px-3 py-1.5 text-xs font-medium transition-colors duration-150
                    {{ $tab === $key ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-surface hover:text-ink' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($tab === 'contracts')
        @include('admin.clients._contracts_table', ['contracts' => $contracts])
    @elseif ($tab === 'archived')
        @include('admin.clients._archived_table', ['clients' => $clients])
    @else
        @include('admin.clients._clients_table', ['clients' => $clients])
    @endif
@endsection
