@extends('layouts.app')

@section('title', 'My Job Orders')

@php use App\Enums\JobOrderStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">My Job Orders</h1>
            <p class="mt-1 text-xs text-ink-muted">Assigned visits, status updates, and material usage.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('field.dashboard')">Dashboard</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Jobs Today" :value="$stats['today']" variant="brand" />
        <x-ui.stat-card label="Open Jobs" :value="$stats['open']" variant="info" />
        <x-ui.stat-card label="Completed This Month" :value="$stats['completed_month']" variant="success" />
        <x-ui.stat-card label="Delayed" :value="$stats['delayed']" :variant="$stats['delayed'] > 0 ? 'danger' : 'default'" />
    </div>

    <div class="mb-6 flex flex-wrap gap-1">
        @foreach (['upcoming' => 'Upcoming', 'today' => 'Today', 'completed' => 'Completed', 'all' => 'All'] as $key => $label)
            <a
                href="{{ route('field.job-orders', ['view' => $key]) }}"
                class="rounded-sm px-3 py-1.5 text-xs font-medium transition-colors duration-150
                    {{ $view === $key ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-surface hover:text-ink' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date / window</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Service</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($jobs as $job)
                        <tr class="transition-colors hover:bg-brand-50/40">
                            <td class="px-4 py-3 font-mono text-[11px] text-ink">
                                {{ $job->scheduled_date?->format('M d, Y') }}
                                <span class="block text-ink-muted">{{ $job->service_window }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <p class="font-medium text-ink">{{ $job->client_name }}</p>
                                <p class="mt-0.5 text-[10px] text-ink-muted">{{ $job->service_address }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $job->service_type }}</td>
                            <td class="px-4 py-3 text-xs">
                                <x-ui.badge :variant="match ($job->route_status) {
                                    JobOrderStatus::Completed => 'success',
                                    JobOrderStatus::Delayed => 'danger',
                                    JobOrderStatus::OnSite, JobOrderStatus::EnRoute => 'info',
                                    default => 'default',
                                }">{{ $job->route_status->value }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button variant="ghost" size="sm" :href="route('field.job-orders.show', $job)">Open</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2">
                                <x-ui.empty-state title="No jobs in this view" message="Switch tabs or wait for dispatch to assign work." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <x-ui.pagination :paginator="$jobs" />
    </div>
@endsection
