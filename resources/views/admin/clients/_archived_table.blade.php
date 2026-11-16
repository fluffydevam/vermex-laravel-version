@php use App\Enums\ClientStatus; @endphp

<div class="overflow-hidden rounded-md border border-border bg-surface shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-border bg-surface-muted">
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Client</th>
                    <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-ink-muted">Contact</th>
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
                            <x-ui.badge variant="danger">{{ ucfirst($client->status->value) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink/90">{{ $client->city }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <x-ui.button variant="ghost" size="sm" :href="route($showRoute, $client)">View</x-ui.button>
                                @can('restore', $client)
                                    <form action="{{ route('admin.clients.restore', $client) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <x-ui.button variant="success" size="sm" type="submit">Restore</x-ui.button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2">
                            <x-ui.empty-state title="No archived clients" message="Archived clients will appear here." />
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
