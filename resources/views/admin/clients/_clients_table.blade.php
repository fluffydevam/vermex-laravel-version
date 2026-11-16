@php use App\Enums\ClientStatus; use App\Enums\ClientType; @endphp

<div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-border bg-surface-muted">
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contact</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Type</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Status</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">City</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($clients as $client)
                    <tr class="transition-colors duration-150 hover:bg-brand-50/40">
                        <td class="px-4 py-3 text-xs">
                            <a href="{{ route($showRoute, $client) }}" class="font-medium text-link hover:text-link-hover">
                                {{ $client->client_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] text-ink/90">
                            {{ $client->first_name }} {{ $client->last_name }}<br>
                            <span class="text-ink-muted">{{ $client->phone_number }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <x-ui.badge :variant="$client->client_type === ClientType::Commercial ? 'brand' : 'default'">
                                {{ $client->client_type->value }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <x-ui.badge :variant="$client->status === ClientStatus::Active ? 'success' : 'danger'">
                                {{ ucfirst($client->status->value) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink/90">{{ $client->city }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <x-ui.button variant="ghost" size="sm" :href="route($showRoute, $client)">View</x-ui.button>
                                @can('update', $client)
                                    <x-ui.button variant="ghost" size="sm" :href="route('admin.clients.edit', $client)">Edit</x-ui.button>
                                @endcan
                                @can('archive', $client)
                                    <form action="{{ route('admin.clients.archive', $client) }}" method="POST" onsubmit="return confirm('Archive this client?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button variant="danger" size="sm" type="submit">Archive</x-ui.button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2">
                            <x-ui.empty-state title="No clients found" message="Try adjusting your filters or register a new client." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <x-ui.pagination :paginator="$clients" />
</div>
