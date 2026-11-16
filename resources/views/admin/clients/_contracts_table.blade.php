@php use App\Enums\ContractStatus; @endphp

<div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-border bg-surface-muted">
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contract</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Term</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Value</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Balance</th>
                    @can('create', App\Models\Contract::class)
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Actions</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($contracts as $contract)
                    <tr class="transition-colors duration-150 hover:bg-brand-50/40">
                        <td class="px-4 py-3 text-xs font-medium text-ink">{{ $contract->contract_name }}</td>
                        <td class="px-4 py-3 text-xs text-ink/90">
                            @if ($contract->client)
                                <a href="{{ route($showRoute, $contract->client) }}" class="text-link hover:text-link-hover">
                                    {{ $contract->client->client_name }}
                                </a>
                            @else
                                <span class="text-ink-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] text-ink-muted">
                            {{ $contract->contract_start_date?->format('M d, Y') ?? '—' }}
                            →
                            {{ $contract->contract_end_date?->format('M d, Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <x-ui.badge :variant="match ($contract->contract_status) {
                                ContractStatus::Active => 'success',
                                ContractStatus::ExpiringSoon => 'warning',
                                ContractStatus::Expired, ContractStatus::Cancelled => 'danger',
                                default => 'default',
                            }">
                                {{ str_replace('_', ' ', ucfirst($contract->contract_status->value)) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-[11px] text-ink">₱{{ number_format((float) $contract->contract_value, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-[11px] {{ $contract->final_balance > 0 ? 'text-danger' : 'text-ink-muted' }}">
                            ₱{{ number_format($contract->final_balance, 2) }}
                        </td>
                        @can('create', App\Models\Contract::class)
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    @if ($contract->contract_status !== ContractStatus::Archived)
                                        <form action="{{ route('admin.contracts.archive', $contract) }}" method="POST" onsubmit="return confirm('Archive this contract?')">
                                            @csrf
                                            @method('PUT')
                                            <x-ui.button variant="ghost" size="sm" type="submit">Archive</x-ui.button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.contracts.restore', $contract) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <x-ui.button variant="success" size="sm" type="submit">Restore</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('create', App\Models\Contract::class) ? 7 : 6 }}" class="px-4 py-2">
                            <x-ui.empty-state title="No contracts found" message="Create a contract from a client's detail page." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <x-ui.pagination :paginator="$contracts" />
</div>
