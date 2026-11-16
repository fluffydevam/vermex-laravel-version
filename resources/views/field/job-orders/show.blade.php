@extends('layouts.app')

@section('title', 'Job Order #' . str_pad((string) $job->id, 4, '0', STR_PAD_LEFT))

@php use App\Enums\JobOrderStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold tracking-tight text-ink">{{ $job->client_name }}</h1>
                <x-ui.badge :variant="match ($job->route_status) {
                    JobOrderStatus::Completed => 'success',
                    JobOrderStatus::Delayed => 'danger',
                    JobOrderStatus::OnSite, JobOrderStatus::EnRoute => 'info',
                    default => 'default',
                }">{{ $job->route_status->value }}</x-ui.badge>
                <x-ui.badge :variant="match ($job->priority->value) {
                    'Urgent' => 'danger',
                    'High' => 'warning',
                    default => 'default',
                }">{{ $job->priority->value }}</x-ui.badge>
            </div>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">
                Job #{{ str_pad((string) $job->id, 4, '0', STR_PAD_LEFT) }} ·
                {{ $job->scheduled_date?->format('M d, Y') }} · {{ $job->service_window }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="ghost" :href="auth()->user()->isAdmin() ? route('admin.job-orders') : route('field.job-orders')">Back</x-ui.button>
            @if (auth()->user()->isAdmin())
                <x-ui.button variant="ghost" :href="route('admin.job-orders.edit', $job)">Edit</x-ui.button>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6">
            <x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Visit details</h2>
                <dl class="grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
                    <div>
                        <dt class="text-ink-muted">Service type</dt>
                        <dd class="mt-0.5 font-medium text-ink">{{ $job->service_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-ink-muted">Technician</dt>
                        <dd class="mt-0.5 font-medium text-ink">{{ $job->technician?->full_name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-ink-muted">Service address</dt>
                        <dd class="mt-0.5 text-ink">{{ $job->service_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-ink-muted">Contract</dt>
                        <dd class="mt-0.5 text-ink">{{ $job->contract?->contract_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-ink-muted">Time in</dt>
                        <dd class="mt-0.5 font-mono text-ink">{{ $job->time_in ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-ink-muted">Time out</dt>
                        <dd class="mt-0.5 font-mono text-ink">{{ $job->time_out ?? '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Materials used</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-border bg-surface-muted">
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Item</th>
                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Quantity</th>
                                <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Logged</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($job->materials as $material)
                                <tr>
                                    <td class="px-4 py-3 text-xs font-medium text-ink">{{ $material->inventoryItem?->item_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">
                                        {{ number_format((float) $material->quantity_used, 2) }} {{ $material->unit_used }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">{{ $material->logged_at?->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-2">
                                        <x-ui.empty-state title="No materials logged" message="Record chemicals or devices used on this visit." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-ink-muted">Area findings</h2>
                @if (! empty($job->area_findings))
                    <ul class="space-y-2 text-xs text-ink">
                        @foreach ($job->area_findings as $finding)
                            <li class="rounded-sm border border-border bg-surface-muted px-3 py-2">
                                <span class="font-medium">{{ $finding['area'] ?? 'Area' }}</span>
                                @if (! empty($finding['action']))
                                    <span class="text-ink-muted"> — {{ $finding['action'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-ink-muted">No findings recorded.</p>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Update status</h2>
                <form method="POST" action="{{ route('field.job-orders.status', $job) }}" class="space-y-4">
                    @csrf
                    <x-ui.select label="Status" name="route_status" :value="$job->route_status" required :placeholder="null" :options="[
                        'Scheduled' => 'Scheduled',
                        'En route' => 'En route',
                        'On site' => 'On site',
                        'Completed' => 'Completed',
                        'Delayed' => 'Delayed',
                    ]" />
                    <x-ui.textarea label="Comments" name="comments" :value="$job->comments" rows="3" />
                    <x-ui.button variant="primary" type="submit" class="w-full">Save status</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card>
                <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-muted">Log material usage</h2>

                @if ($job->route_status === JobOrderStatus::Scheduled)
                    <p class="text-xs text-ink-muted">Start the job (En route or On site) before recording materials.</p>
                @elseif ($inventoryItems->isEmpty())
                    <p class="text-xs text-ink-muted">No inventory items are currently in stock.</p>
                @else
                    <form method="POST" action="{{ route('field.job-orders.materials', $job) }}" class="space-y-4">
                        @csrf
                        <x-ui.select label="Item" name="inventory_item_id" required :placeholder="null" :options="$inventoryItems->mapWithKeys(fn ($item) => [$item->id => $item->item_name . ' (' . number_format((float) $item->quantity_in_stock, 2) . ' ' . $item->unit . ')'])->all()" />
                        <x-ui.input label="Quantity used" name="quantity_used" type="number" step="0.01" min="0.01" required />
                        <x-ui.input label="Remarks" name="remarks" placeholder="Optional note" />
                        <x-ui.button variant="primary" type="submit" class="w-full">Record usage</x-ui.button>
                    </form>
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
