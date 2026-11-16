@extends('layouts.app')

@section('title', 'Edit Inspection')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Edit Inspection</h1>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">
                Inspection #{{ str_pad((string) $inspection->id, 4, '0', STR_PAD_LEFT) }} · {{ $inspection->client_name }}
            </p>
        </div>
        <div class="flex gap-2">
            <x-ui.button variant="ghost" :href="route('admin.inspections.show', $inspection)">View</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.inspections')">Back to inspections</x-ui.button>
        </div>
    </div>

    <div class="max-w-5xl">
        @if ($errors->any())
            <div class="mb-6"><x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert></div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.inspections.update', $inspection) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.inspections._form', ['locked' => false])

                <div class="flex gap-3 border-t border-border pt-6">
                    <x-ui.button variant="primary" type="submit">Save inspection</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.inspections.show', $inspection)">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
