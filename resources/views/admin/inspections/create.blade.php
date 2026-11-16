@extends('layouts.app')

@section('title', 'New Inspection')

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-ink">New Site Inspection</h1>
            <p class="mt-1 text-xs text-ink-muted">Record a scheduled or completed inspection visit.</p>
        </div>
        <x-ui.button variant="ghost" :href="route('admin.inspections')">Back to inspections</x-ui.button>
    </div>

    <div class="max-w-5xl">
        @if ($errors->any())
            <div class="mb-6"><x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert></div>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.inspections.store') }}" class="space-y-6">
                @csrf

                @include('admin.inspections._form', ['inspection' => null, 'locked' => false])

                <div class="flex gap-3 border-t border-border pt-6">
                    <x-ui.button variant="primary" type="submit">Create inspection</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('admin.inspections')">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
