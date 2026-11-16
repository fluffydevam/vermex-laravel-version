@extends('layouts.app')

@section('title', 'New Job Order')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">New Job Order</h1>
            <p class="mt-1 text-xs text-ink-muted">Schedule a visit and assign a field technician.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.job-orders')">Back to dispatch</x-ui.button>
    </div>

    <div class="max-w-4xl">
        @if ($errors->any())
            <div class="mb-6">
                <x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert>
            </div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.job-orders.store') }}" class="space-y-6">
                @csrf

                <x-ui.alert type="info" title="Client snapshots">
                    The client name and service address are copied onto the job order when saved, so later client edits do not change printed records.
                </x-ui.alert>

                @include('admin.job-orders._form')

                <div class="flex gap-3 border-t border-border pt-6">
                    <x-ui.button variant="primary" type="submit">Schedule job order</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.job-orders')">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
