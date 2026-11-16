@extends('layouts.app')

@section('title', 'Dashboard')

@php use App\Enums\JobOrderStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Dashboard</h1>
            <p class="mt-1 text-xs text-ink-muted">Operations overview for {{ now()->format('l, F j, Y') }}.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="ghost" :href="route('admin.clients.create')">Add client</x-ui.button>
            <x-ui.button variant="primary" :href="route('admin.clients')">Clients &amp; contracts</x-ui.button>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Active Clients" :value="$stats['clients_active']" variant="brand" />
        <x-ui.stat-card label="Active Contracts" :value="$stats['contracts_active']" variant="success" />
        <x-ui.stat-card label="Collected This Month" :value="'₱' . number_format($stats['collected_month'], 2)" variant="info" />
        <x-ui.stat-card label="Low Stock Items" :value="$stats['low_stock']" :variant="$stats['low_stock'] > 0 ? 'danger' : 'default'" />
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Jobs Today" :value="$stats['jobs_today']" />
        <x-ui.stat-card label="Open Job Orders" :value="$stats['jobs_pending']" />
        <x-ui.stat-card label="Pending Inspections" :value="$stats['inspections_pending']" />
        <x-ui.stat-card label="Contracts Expiring" :value="$stats['contracts_expiring']" :variant="$stats['contracts_expiring'] > 0 ? 'warning' : 'default'" />
    </div>

    <x-ui.card :padded="false">
        <div class="flex items-center justify-between border-b border-border px-5 py-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Upcoming jobs</h2>
            <span class="font-mono text-[11px] text-ink-muted">{{ $stats['users_active'] }} active users</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Service</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Technician</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($upcomingJobs as $job)
                        <tr class="transition-colors hover:bg-brand-50/40">
                            <td class="px-4 py-3 font-mono text-[11px] text-ink">{{ $job->scheduled_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $job->client?->client_name ?? $job->client_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $job->service_type }}</td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $job->technician?->full_name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-xs">
                                <x-ui.badge :variant="match ($job->route_status) {
                                    JobOrderStatus::Completed => 'success',
                                    JobOrderStatus::Delayed => 'danger',
                                    JobOrderStatus::OnSite, JobOrderStatus::EnRoute => 'info',
                                    default => 'default',
                                }">{{ $job->route_status->value }}</x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2">
                                <x-ui.empty-state title="No upcoming jobs" message="Scheduled job orders will appear here once dispatch is in use." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endsection
