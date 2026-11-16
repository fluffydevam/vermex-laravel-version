@extends('layouts.app')

@php
    $currentRoute = 'styleguide';
@endphp

@section('styles')
    <style>
        .sg-section { @apply py-8 border-b border-border; }
        .sg-title { @apply text-xs font-semibold uppercase tracking-wider text-ink-muted mb-4; }
        .sg-grid { @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4; }
        .sg-note { @apply text-[11px] text-ink-muted mt-1 max-w-prose; }
    </style>
@endsection

@section('content')
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 border-b border-border pb-6 mb-8">
        <div>
            <h1 class="text-lg font-bold text-ink tracking-tight">Design System Styleguide</h1>
            <p class="text-xs text-ink-muted mt-1">Vermex Pest Solutions · Minimalismo Funcional B2B + Vermex crimson</p>
        </div>
        <x-ui.button variant="ghost" size="sm" href="/">Home</x-ui.button>
    </div>

    <!-- Colors -->
    <section class="sg-section">
        <h2 class="sg-title">Colors</h2>
        <div class="sg-grid">
            <div class="p-4 rounded-md border border-border bg-surface">
                <div class="w-full h-10 rounded-sm bg-brand-600 mb-2"></div>
                <div class="text-[11px] font-medium text-ink">Brand / Primary #d32f2f</div>
                <div class="text-[10px] text-ink-muted">Primary actions, active states</div>
            </div>
            <div class="p-4 rounded-md border border-border bg-surface">
                <div class="w-full h-10 rounded-sm bg-brand-50 mb-2 border border-brand-100"></div>
                <div class="text-[11px] font-medium text-brand-ink">Brand Tint #fdf2f2</div>
                <div class="text-[10px] text-ink-muted">Vermex-subtle backgrounds</div>
            </div>
            <div class="p-4 rounded-md border border-border bg-surface">
                <div class="w-full h-10 rounded-sm bg-success mb-2"></div>
                <div class="text-[11px] font-medium text-success-strong">Success #28a745</div>
            </div>
            <div class="p-4 rounded-md border border-border bg-surface">
                <div class="w-full h-10 rounded-sm bg-warning mb-2 border border-yellow-200"></div>
                <div class="text-[11px] font-medium text-warning-strong">Warning #ffc107</div>
            </div>
            <div class="p-4 rounded-md border border-border bg-surface">
                <div class="w-full h-10 rounded-sm bg-danger mb-2"></div>
                <div class="text-[11px] font-medium text-danger-strong">Danger #dc3545</div>
            </div>
            <div class="p-4 rounded-md border border-border bg-surface">
                <div class="w-full h-10 rounded-sm bg-sidebar mb-2"></div>
                <div class="text-[11px] font-medium text-ink">Sidebar #2b110d</div>
            </div>
        </div>
    </section>

    <!-- Typography -->
    <section class="sg-section">
        <h2 class="sg-title">Typography</h2>
        <div class="space-y-4">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-muted">Display / H1</p>
            <h1 class="text-xl font-bold text-ink tracking-tight">Vermex Pest Solutions</h1>
            <p class="text-lg font-semibold text-ink">H2 — Section heading</p>
            <p class="text-sm text-ink/90">Body — Inter 1rem / 1.6, max 72ch</p>
            <p class="text-xs text-ink-muted">Small / caption — 0.875rem</p>
            <p class="font-mono text-xs text-ink/80">Mono — JetBrains Mono for IDs, amounts, dates</p>
        </div>
    </section>

    <!-- Buttons -->
    <section class="sg-section">
        <h2 class="sg-title">Buttons</h2>
        <div class="flex flex-wrap gap-3">
            <x-ui.button variant="primary">Primary</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
            <x-ui.button variant="danger">Danger</x-ui.button>
            <x-ui.button variant="success">Success</x-ui.button>
            <x-ui.button variant="outline">Outline</x-ui.button>
            <x-ui.button variant="primary" size="sm">Small</x-ui.button>
            <x-ui.button variant="primary" size="lg">Large</x-ui.button>
            <x-ui.button variant="primary" disabled>Disabled</x-ui.button>
            <x-ui.button variant="primary" loading>Loading</x-ui.button>
        </div>
        <p class="sg-note">Primary = Vermex crimson fill, hover darkens 8%, active tactile press.</p>
    </section>

    <!-- Form controls -->
    <section class="sg-section">
        <h2 class="sg-title">Form controls</h2>
        <div class="sg-grid">
            <x-ui.input label="Username" name="username" placeholder="admin" />
            <x-ui.input label="Password" type="password" name="password" />
            <x-ui.input label="Email" type="email" name="email" error="Invalid email format." />
            <x-ui.select label="Role" name="role" :options="['Admin' => 'Admin', 'Billing Officer' => 'Billing Officer', 'Field Technician' => 'Field Technician', 'Chemical Custodian' => 'Chemical Custodian']" />
            <x-ui.textarea label="Remarks" name="remarks" rows="3" />
            <x-ui.checkbox name="terms" label="I accept the terms" />
        </div>
    </section>

    <!-- Cards & stat cards -->
    <section class="sg-section">
        <h2 class="sg-title">Cards & Stat cards</h2>
        <div class="sg-grid">
            <x-ui.card>
                <h3 class="text-sm font-semibold text-ink">Basic card</h3>
                <p class="text-xs text-ink-muted mt-1">Surface background, 1px border, subtle shadow.</p>
            </x-ui.card>
            <x-ui.stat-card label="Active Clients" value="124" variant="default" />
            <x-ui.stat-card label="Total Collected" value="₱45,200.00" variant="brand" />
            <x-ui.stat-card label="Low Stock" value="6" variant="warning" :trend="['up' => true, 'label' => '+2 since yesterday']" />
            <x-ui.stat-card label="Overdue" value="3" variant="danger" />
        </div>
    </section>

    <!-- Badges -->
    <section class="sg-section">
        <h2 class="sg-title">Badges</h2>
        <div class="flex flex-wrap gap-2">
            <x-ui.badge variant="default">Default</x-ui.badge>
            <x-ui.badge variant="brand">Brand</x-ui.badge>
            <x-ui.badge variant="success">Active</x-ui.badge>
            <x-ui.badge variant="warning">Low Stock</x-ui.badge>
            <x-ui.badge variant="danger">Expired</x-ui.badge>
            <x-ui.badge variant="info">Pending</x-ui.badge>
        </div>
    </section>

    <!-- Alerts -->
    <section class="sg-section">
        <h2 class="sg-title">Alerts</h2>
        <div class="space-y-3 max-w-sm">
            <x-ui.alert type="success" title="Saved">Record saved successfully.</x-ui.alert>
            <x-ui.alert type="error" title="Error">Something went wrong.</x-ui.alert>
            <x-ui.alert type="warning" title="Warning">Review before submitting.</x-ui.alert>
            <x-ui.alert type="info" title="Info">Read-only view.</x-ui.alert>
        </div>
    </section>

    <!-- Table -->
    <section class="sg-section">
        <h2 class="sg-title">Table</h2>
        <x-ui.table :columns="['Client', 'Type', 'Status', 'Value']" :rows="[
            ['Client' => 'Deca Homes', 'Type' => 'Commercial', 'Status' => 'Active', 'Value' => '₱50,000'],
            ['Client' => 'Gaisano Mall', 'Type' => 'Commercial', 'Status' => 'Expiring Soon', 'Value' => '₱120,000'],
            ['Client' => 'Residence B', 'Type' => 'Residential', 'Status' => 'Archived', 'Value' => '₱0'],
        ]" />
    </section>

    <!-- Tabs -->
    <section class="sg-section">
        <h2 class="sg-title">Tabs</h2>
        <x-ui.tabs :items="['clients', 'contracts', 'archived']" active="clients" />
    </section>

    <!-- Empty state -->
    <section class="sg-section">
        <h2 class="sg-title">Empty state</h2>
        <div class="max-w-sm">
            <x-ui.empty-state title="No records found" message="Try adjusting your filters." />
        </div>
    </section>

    <!-- Modal -->
    <section class="sg-section">
        <h2 class="sg-title">Modal</h2>
        <x-ui.button variant="primary" onclick="openModal('demo-modal')">Open modal</x-ui.button>
        <x-ui.modal id="demo-modal" title="Confirm action" :open="false">
            <p class="text-sm text-ink/90">This is a modal built from the UI kit.</p>
            <div class="mt-6 flex gap-3">
                <x-ui.button variant="ghost" onclick="closeModal('demo-modal')">Cancel</x-ui.button>
                <x-ui.button variant="primary" onclick="closeModal('demo-modal')">Confirm</x-ui.button>
            </div>
        </x-ui.modal>
    </section>
@endsection

@push('scripts')
    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            const ov = document.getElementById(id + '-overlay');
            el.classList.remove('hidden', 'scale-95', 'opacity-0');
            el.classList.add('scale-100', 'opacity-100');
            ov.classList.remove('hidden');
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            const ov = document.getElementById(id + '-overlay');
            el.classList.remove('scale-100', 'opacity-100');
            el.classList.add('scale-95', 'opacity-0');
            setTimeout(() => el.classList.add('hidden'), 200);
            ov.classList.add('hidden');
        }
    </script>
@endpush
