@extends('layouts.app')

@section('title', 'Edit Job Order')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">Edit Job Order</h1>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">
                Job #{{ str_pad((string) $job->id, 4, '0', STR_PAD_LEFT) }} · {{ $job->client_name }}
            </p>
        </div>
        <div class="flex gap-2">
            <x-ui.button variant="ghost" :href="route('field.job-orders.show', $job)">View</x-ui.button>
            <x-ui.button variant="ghost" :href="route('admin.job-orders')">Back to dispatch</x-ui.button>
        </div>
    </div>

    <div class="max-w-4xl">
        @if ($errors->any())
            <div class="mb-6">
                <x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert>
            </div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.job-orders.update', $job) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.job-orders._form')

                <div class="flex gap-3 border-t border-border pt-6">
                    <x-ui.button variant="primary" type="submit">Save changes</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.job-orders')">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
