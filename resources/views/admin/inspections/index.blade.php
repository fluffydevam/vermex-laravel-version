@extends('layouts.app')

@section('title', 'Inspections')

@php use App\Enums\InspectionStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Inspections</h1>
            <p class="mt-1 text-xs text-ink-muted">Site inspection reports, device counts, and findings.</p>
        </div>
        <x-ui.button variant="primary" :href="route('admin.inspections.create')">New inspection</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Pending Visits" :value="$stats['pending']" :variant="$stats['pending'] > 0 ? 'warning' : 'success'" />
        <x-ui.stat-card label="Complete" :value="$stats['complete']" variant="success" />
        <x-ui.stat-card label="Pests Found" :value="$stats['pests']" :variant="$stats['pests'] > 0 ? 'danger' : 'default'" />
        <x-ui.stat-card label="This Month" :value="$stats['month']" variant="info" />
    </div>

    <div class="mb-6 rounded-md border border-border bg-surface p-4 shadow-card">
        <form method="GET" action="{{ route('admin.inspections') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.input label="Search" name="search" :value="$search" placeholder="Client or address…" />
            <x-ui.select label="Status" name="status" :value="$status" :options="[
                '' => 'All statuses',
                'Pending Visit' => 'Pending Visit',
                'Inspection Complete' => 'Inspection Complete',
                'Inspection Complete (Pests Found)' => 'Pests Found',
            ]" />
            <x-ui.select label="Technician" name="technician" :value="$technicianId" :options="['' => 'All technicians'] + $technicians->mapWithKeys(fn ($t) => [$t->id => $t->full_name])->all()" />
            <div class="flex gap-2">
                <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
                <x-ui.button variant="ghost" :href="route('admin.inspections')">Reset</x-ui.button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-border bg-surface-muted">
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Technician</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($inspections as $inspection)
                        <tr class="transition-colors hover:bg-brand-50/40">
                            <td class="px-4 py-3 font-mono text-[11px] text-ink">{{ $inspection->inspection_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs">
                                <p class="font-medium text-ink">{{ $inspection->client_name }}</p>
                                <p class="mt-0.5 text-[10px] text-ink-muted">{{ $inspection->service_address }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink/90">{{ $inspection->technician?->full_name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-xs">
                                <x-ui.badge :variant="match ($inspection->inspection_status) {
                                    InspectionStatus::PestsFound => 'danger',
                                    InspectionStatus::InspectionComplete => 'success',
                                    default => 'warning',
                                }">{{ $inspection->inspection_status->value }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button variant="ghost" size="sm" :href="route('admin.inspections.show', $inspection)">View</x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" :href="route('admin.inspections.edit', $inspection)">Edit</x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2">
                                <x-ui.empty-state title="No inspections" message="Create an inspection or adjust the filters." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><x-ui.pagination :paginator="$inspections" /></div>
@endsection
