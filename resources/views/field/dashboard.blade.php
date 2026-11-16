@extends('layouts.app')

@section('title', 'Field Dashboard')

@php use App\Enums\JobOrderStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Field Dashboard</h1>
            <p class="mt-1 text-xs text-ink-muted">Your assigned jobs for {{ now()->format('l, F j') }}.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Jobs Today" :value="$stats['today']" variant="brand" />
        <x-ui.stat-card label="Open Jobs" :value="$stats['open']" variant="info" />
        <x-ui.stat-card label="Completed This Month" :value="$stats['completed_month']" variant="success" />
        <x-ui.stat-card label="Pending Inspections" :value="$stats['inspections_pending']" :variant="$stats['inspections_pending'] > 0 ? 'warning' : 'default'" />
    </div>

    <x-ui.card :padded="false">
        <div class="border-b border-border px-5 py-4">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Assigned job orders</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Service</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Window</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($jobs as $job)
                        <tr class="transition-colors hover:bg-brand-50/40">
                            <td class="px-4 py-3 font-mono text-[11px] text-ink">{{ $job->scheduled_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $job->client?->client_name ?? $job->client_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $job->service_type }}</td>
                            <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $job->service_window }}</td>
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
                                <x-ui.empty-state title="No assigned jobs" message="Job orders dispatched to you will appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@endsection
