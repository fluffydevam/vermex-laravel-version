@extends('layouts.app')

@section('title', 'My Inspections')

@php use App\Enums\InspectionStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">My Inspections</h1>
            <p class="mt-1 text-xs text-ink-muted">Assigned site inspections and completed reports.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('field.dashboard')">Dashboard</x-ui.button>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Pending Visits" :value="$stats['pending']" :variant="$stats['pending'] > 0 ? 'warning' : 'success'" />
        <x-ui.stat-card label="Complete" :value="$stats['complete']" variant="success" />
        <x-ui.stat-card label="Pests Found" :value="$stats['pests']" :variant="$stats['pests'] > 0 ? 'danger' : 'default'" />
        <x-ui.stat-card label="This Month" :value="$stats['month']" variant="info" />
    </div>

    <div class="mb-6 flex flex-wrap gap-1">
        @foreach (['' => 'All', 'Pending Visit' => 'Pending', 'Inspection Complete' => 'Complete', 'Inspection Complete (Pests Found)' => 'Pests Found'] as $key => $label)
            <a
                href="{{ route('field.inspections', $key === '' ? [] : ['status' => $key]) }}"
                class="rounded-sm px-3 py-1.5 text-xs font-medium transition-colors duration-150
                    {{ $status === $key ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-surface hover:text-ink' }}"
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
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Date</th>
                        <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
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
                            <td class="px-4 py-3 text-xs">
                                <x-ui.badge :variant="match ($inspection->inspection_status) {
                                    InspectionStatus::PestsFound => 'danger',
                                    InspectionStatus::InspectionComplete => 'success',
                                    default => 'warning',
                                }">{{ $inspection->inspection_status->value }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-ui.button variant="ghost" size="sm" :href="route('field.inspections.show', $inspection)">
                                    {{ $inspection->inspection_status === InspectionStatus::PendingVisit ? 'Open' : 'Review' }}
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2">
                                <x-ui.empty-state title="No inspections in this view" message="Switch tabs or wait for dispatch to assign inspections." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <x-ui.pagination :paginator="$inspections" />
    </div>
@endsection
