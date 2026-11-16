@extends('layouts.app')

@section('title', 'Inspection #' . str_pad((string) $inspection->id, 4, '0', STR_PAD_LEFT))

@php use App\Enums\InspectionStatus; @endphp

@section('content')
    <div class="mb-8 flex flex-col gap-6 border-b border-border pb-6 lg:flex-row lg:items-start lg:justify-between print:hidden">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold tracking-tight text-ink">{{ $inspection->client_name }}</h1>
                <x-ui.badge :variant="match ($inspection->inspection_status) {
                    InspectionStatus::PestsFound => 'danger',
                    InspectionStatus::InspectionComplete => 'success',
                    default => 'warning',
                }">{{ $inspection->inspection_status->value }}</x-ui.badge>
            </div>
            <p class="mt-1 font-mono text-[11px] text-ink-muted">
                Inspection #{{ str_pad((string) $inspection->id, 4, '0', STR_PAD_LEFT) }} ·
                {{ $inspection->inspection_date?->format('M d, Y') ?? 'No date' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="ghost" :href="route('field.inspections')">Back</x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="window.print()">Print / PDF</x-ui.button>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 print:hidden">
            <x-ui.alert type="error" title="Please review the form">{{ $errors->first() }}</x-ui.alert>
        </div>
    @endif

    <div class="max-w-5xl">
        <x-ui.card>
            <form method="POST" action="{{ route('field.inspections.update', $inspection) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.inspections._form', ['locked' => true])

                <div class="flex gap-3 border-t border-border pt-6 print:hidden">
                    <x-ui.button variant="primary" type="submit">Save report</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('field.inspections')">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
