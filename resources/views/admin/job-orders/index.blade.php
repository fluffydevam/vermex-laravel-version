@extends('layouts.app')

@section('title', 'Dispatch')

@php use App\Enums\JobOrderStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Dispatch</h1>
            <p class="mt-1 text-xs text-ink-muted">Schedule job orders and assign field technicians.</p>
        </div>
        <x-ui.button variant="primary" :href="route('admin.job-orders.create')">New job order</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Scheduled Today" :value="$stats['today']" variant="brand" />
        <x-ui.stat-card label="Open Jobs" :value="$stats['open']" variant="info" />
        <x-ui.stat-card label="Completed This Month" :value="$stats['completed_month']" variant="success" />
        <x-ui.stat-card label="Delayed" :value="$stats['delayed']" :variant="$stats['delayed'] > 0 ? 'danger' : 'default'" />
    </div>

    <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card">
        <form method="GET" action="{{ route('admin.job-orders') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <x-ui.input label="Search" name="search" :value="$search" placeholder="Client, service, address…" />
            <x-ui.select label="Status" name="status" :value="$status" :options="[
                '' => 'All statuses',
                'Scheduled' => 'Scheduled',
                'En route' => 'En route',
                'On site' => 'On site',
                'Completed' => 'Completed',
                'Delayed' => 'Delayed',
            ]" />
            <x-ui.select label="Technician" name="technician" :value="$technicianId" :options="['' => 'All technicians'] + $technicians->mapWithKeys(fn ($t) => [$t->id => $t->full_name])->all()" />
            <x-ui.input label="Date" name="date" type="date" :value="$date" />
            <div class="flex gap-2">
                <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
                <x-ui.button variant="ghost" :href="route('admin.job-orders')">Reset</x-ui.button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date / window</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Service</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Technician</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Priority</th>
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
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $job->technician?->full_name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-xs">
                                <x-ui.badge :variant="match ($job->priority->value) {
                                    'Urgent' => 'danger',
                                    'High' => 'warning',
                                    default => 'default',
                                }">{{ $job->priority->value }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <x-ui.badge :variant="match ($job->route_status) {
                                    JobOrderStatus::Completed => 'success',
                                    JobOrderStatus::Delayed => 'danger',
                                    JobOrderStatus::OnSite, JobOrderStatus::EnRoute => 'info',
                                    default => 'default',
                                }">{{ $job->route_status->value }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button variant="ghost" size="sm" :href="route('field.job-orders.show', $job)">View</x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" :href="route('admin.job-orders.edit', $job)">Edit</x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-2">
                                <x-ui.empty-state title="No job orders" message="Schedule a job order or adjust the filters." />
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
