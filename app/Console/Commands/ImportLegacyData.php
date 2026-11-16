<?php

namespace App\Console\Commands;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\ContractStatus;
use App\Enums\InspectionStatus;
use App\Enums\InventoryCategory;
use App\Enums\JobOrderPriority;
use App\Enums\JobOrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportLegacyData extends Command
{
    protected $signature = 'vermex:import-legacy
                            {--execute : Write the prepared records into the target database}
                            {--force : Skip the safety confirmation when the target already contains data}';

    protected $description = 'Import legacy Vermex data from the read-only legacy connection (dry-run by default).';

    private array $warnings = [];

    private array $counts = [];

    public function handle(): int
    {
        if (! $this->legacyIsReachable()) {
            return self::FAILURE;
        }

        if ($this->targetIsLegacy()) {
            $this->error('Refusing to run: the target connection points at the legacy database.');

            return self::FAILURE;
        }

        $this->info('Legacy source: ' . config('database.connections.legacy.database'));
        $this->info('Target:        ' . DB::connection()->getDatabaseName());
        $this->newLine();

        $payload = $this->prepare();

        $this->reportPrepared($payload);

        if (! $this->option('execute')) {
            $this->newLine();
            $this->warn('Dry run only. Re-run with --execute to write these records.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && $this->targetHasData()) {
            if (! $this->confirm('The target database already contains data. Continue and upsert by legacy ID?')) {
                $this->warn('Import cancelled.');

                return self::SUCCESS;
            }
        }

        try {
            DB::transaction(function () use ($payload) {
                $this->writeAll($payload);
            });
        } catch (Throwable $e) {
            $this->error('Import failed and was rolled back: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Import complete.');
        $this->reconcile();
        $this->reportWarnings();

        return self::SUCCESS;
    }

    private function legacyIsReachable(): bool
    {
        try {
            DB::connection('legacy')->getPdo();

            return true;
        } catch (Throwable $e) {
            $this->error('Cannot reach the legacy database: ' . $e->getMessage());

            return false;
        }
    }

    private function targetIsLegacy(): bool
    {
        return DB::connection()->getDatabaseName() === config('database.connections.legacy.database');
    }

    private function targetHasData(): bool
    {
        return DB::table('users')->exists()
            || DB::table('clients')->exists()
            || DB::table('contracts')->exists();
    }

    /**
     * Read every legacy table and normalise it into target-shaped rows.
     */
    private function prepare(): array
    {
        $legacy = DB::connection('legacy');

        $now = now();

        $users = [];
        foreach ($legacy->table('users')->orderBy('id')->get() as $row) {
            $users[] = [
                'id' => (int) $row->id,
                'username' => $row->username,
                'email' => $row->email,
                'password' => $row->password,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name ?? '',
                'phone' => $row->phone,
                'role' => $this->enumOr($row->role, UserRole::class, UserRole::Admin, "users#{$row->id} role"),
                'sector_region' => $row->sector_region ?: 'Davao Head Office',
                'status' => $this->normaliseUserStatus($row->status, $row->id),
                'last_login_at' => $this->dateTime($row->last_active),
                'remember_token' => null,
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $clients = [];
        foreach ($legacy->table('clients')->orderBy('id')->get() as $row) {
            $clients[] = [
                'id' => (int) $row->id,
                'client_name' => $row->client_name,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'phone_number' => $row->phone_number,
                'email' => $row->email,
                'street_address' => $row->street_address,
                'barangay' => $row->barangay,
                'city' => $row->city ?: 'Davao City',
                'client_type' => $this->enumOr($row->client_type, ClientType::class, ClientType::Residential, "clients#{$row->id} type"),
                'status' => $this->enumOr($row->status, ClientStatus::class, ClientStatus::Active, "clients#{$row->id} status"),
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $inventory = [];
        foreach ($legacy->table('inventory')->orderBy('id')->get() as $row) {
            $inventory[] = [
                'id' => (int) $row->id,
                'item_name' => $row->item_name,
                'category' => $this->enumOr($row->category, InventoryCategory::class, InventoryCategory::Chemical, "inventory#{$row->id} category"),
                'quantity_in_stock' => (float) $row->quantity_in_stock,
                'unit' => $row->unit ?: 'mL',
                'min_threshold' => (float) $row->min_threshold,
                'unit_cost' => (float) $row->unit_cost,
                'batch_number' => $row->batch_number,
                'storage_location' => $row->storage_location,
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->updated_at) ?? $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $contracts = [];
        $clientIds = array_flip(array_map(fn ($c) => $c['id'], $clients));
        foreach ($legacy->table('contracts')->orderBy('id')->get() as $row) {
            if (! isset($clientIds[(int) $row->client_id])) {
                $this->warn("Skipped contract #{$row->id}: client #{$row->client_id} does not exist.");
                continue;
            }

            $contracts[] = [
                'id' => (int) $row->id,
                'client_id' => (int) $row->client_id,
                'contract_name' => $row->contract_name,
                'contract_status' => $this->enumOr($row->contract_status, ContractStatus::class, ContractStatus::ToBeContracted, "contracts#{$row->id} status"),
                'contract_start_date' => $this->dateOnly($row->contract_start_date),
                'contract_end_date' => $this->dateOnly($row->contract_end_date),
                'contract_value' => (float) $row->contract_value,
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $technicianIds = $this->technicianLookup($users);
        $contractById = [];
        foreach ($contracts as $contract) {
            $contractById[$contract['id']] = $contract;
        }

        $jobOrders = [];
        foreach ($legacy->table('job_orders')->orderBy('id')->get() as $row) {
            $clientId = $row->client_id !== null && isset($clientIds[(int) $row->client_id])
                ? (int) $row->client_id
                : null;

            $contractId = $row->contract_id !== null && isset($contractById[(int) $row->contract_id])
                ? (int) $row->contract_id
                : null;

            $jobOrders[] = [
                'id' => (int) $row->id,
                'client_id' => $clientId,
                'contract_id' => $contractId,
                'technician_id' => $this->matchTechnician($row->assigned_tech, $technicianIds, "job_orders#{$row->id}"),
                'client_name' => $row->client_name,
                'service_address' => $row->location,
                'service_type' => $row->service_type,
                'scheduled_date' => $this->dateOnly($row->scheduled_date),
                'service_window' => $row->service_window,
                'priority' => $this->enumOr($row->priority, JobOrderPriority::class, JobOrderPriority::Standard, "job_orders#{$row->id} priority"),
                'route_status' => $this->enumOr($row->route_status, JobOrderStatus::class, JobOrderStatus::Scheduled, "job_orders#{$row->id} status"),
                'time_in' => $this->timeOnly($row->time_in),
                'time_out' => $this->timeOnly($row->time_out),
                'comments' => filled($row->comments) ? $row->comments : null,
                'area_findings' => $this->jsonOrNull($row->area_findings, "job_orders#{$row->id} area_findings"),
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $clientIdByName = [];
        foreach ($clients as $client) {
            $clientIdByName[$this->nameKey($client['client_name'])] = $client['id'];
        }

        $inspections = [];
        foreach ($legacy->table('site_inspections')->orderBy('id')->get() as $row) {
            $contractId = $row->contract_id !== null && isset($contractById[(int) $row->contract_id])
                ? (int) $row->contract_id
                : null;

            $clientId = $contractId !== null
                ? $contractById[$contractId]['client_id']
                : ($clientIdByName[$this->nameKey($row->client_name)] ?? null);

            $status = $this->enumOr($row->inspection_status, InspectionStatus::class, InspectionStatus::PendingVisit, "site_inspections#{$row->id} status");

            $inspections[] = [
                'id' => (int) $row->id,
                'contract_id' => $contractId,
                'client_id' => $clientId,
                'technician_id' => $this->matchTechnician($row->technician_name, $technicianIds, "site_inspections#{$row->id}"),
                'client_name' => $row->client_name,
                'client_email' => $row->client_email,
                'service_address' => $row->service_address,
                'account_type' => $row->account_type,
                'inspection_status' => $status,
                'inspection_date' => $this->dateOnly($row->inspection_date),
                'time_in' => $this->timeOnly($row->time_in),
                'time_out' => $this->timeOnly($row->time_out),
                'ilt_qty' => (int) $row->ilt_qty,
                'ilt_remarks' => $row->ilt_remarks,
                'rat_cage_qty' => (int) $row->rat_cage_qty,
                'rat_cage_remarks' => $row->rat_cage_remarks,
                'rat_bait_qty' => (int) $row->rat_bait_qty,
                'rat_bait_remarks' => $row->rat_bait_remarks,
                'glue_trap_qty' => (int) $row->glue_trap_qty,
                'glue_trap_remarks' => $row->glue_trap_remarks,
                'vermex_representative' => $row->vermex_representative,
                'client_representative' => $row->client_representative,
                'conditions_json' => $this->jsonOrNull($row->conditions_json, "site_inspections#{$row->id} conditions_json"),
                'findings_json' => $this->jsonOrNull($row->findings_json, "site_inspections#{$row->id} findings_json"),
                'pests_found' => str_contains((string) $status, 'Pests Found'),
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $payments = [];
        $userIds = array_flip(array_map(fn ($u) => $u['id'], $users));
        foreach ($legacy->table('payments')->orderBy('id')->get() as $row) {
            if ($row->contract_id === null || ! isset($contractById[(int) $row->contract_id])) {
                $this->warn("Skipped payment #{$row->id}: contract #{$row->contract_id} does not exist.");
                continue;
            }

            $payments[] = [
                'id' => (int) $row->id,
                'contract_id' => (int) $row->contract_id,
                'user_id' => $row->user_id !== null && isset($userIds[(int) $row->user_id]) ? (int) $row->user_id : null,
                'amount_paid' => (float) $row->amount_paid,
                'payment_type' => $this->enumOr($row->payment_type, PaymentType::class, PaymentType::Adjustment, "payments#{$row->id} type"),
                'payment_method' => $this->enumOr($row->payment_method, PaymentMethod::class, PaymentMethod::Cash, "payments#{$row->id} method"),
                'reference_number' => $row->reference_number,
                'remarks' => $row->remarks,
                'payment_date' => $this->dateTime($row->payment_date) ?? $now,
            ];
        }

        $inventoryIds = array_flip(array_map(fn ($i) => $i['id'], $inventory));
        $jobOrderIds = array_flip(array_map(fn ($j) => $j['id'], $jobOrders));

        $inventoryLogs = [];
        foreach ($legacy->table('inventory_logs')->orderBy('id')->get() as $row) {
            if (! isset($inventoryIds[(int) $row->material_id])) {
                $this->warn("Skipped inventory log #{$row->id}: item #{$row->material_id} does not exist.");
                continue;
            }

            $inventoryLogs[] = [
                'id' => (int) $row->id,
                'inventory_item_id' => (int) $row->material_id,
                'performed_by' => $this->matchPerformer($row->performed_by, $technicianIds, $users, "inventory_logs#{$row->id}"),
                'job_order_id' => $row->job_order_id !== null && isset($jobOrderIds[(int) $row->job_order_id]) ? (int) $row->job_order_id : null,
                'site_inspection_id' => null,
                'action_type' => $row->action_type ?: 'Adjustment',
                'quantity_changed' => (float) $row->quantity_changed,
                'remarks' => $row->remarks,
                'created_at' => $this->dateTime($row->created_at) ?? $now,
                'updated_at' => $this->dateTime($row->created_at) ?? $now,
            ];
        }

        $materials = [];
        foreach ($legacy->table('job_order_materials')->orderBy('id')->get() as $row) {
            if (! isset($jobOrderIds[(int) $row->job_order_id]) || ! isset($inventoryIds[(int) $row->inventory_id])) {
                $this->warn("Skipped job order material #{$row->id}: missing job order or inventory item.");
                continue;
            }

            $materials[] = [
                'id' => (int) $row->id,
                'job_order_id' => (int) $row->job_order_id,
                'inventory_item_id' => (int) $row->inventory_id,
                'quantity_used' => (float) $row->quantity_used,
                'unit_used' => $row->unit_used ?: 'unit',
                'logged_at' => $this->dateTime($row->logged_at) ?? $now,
            ];
        }

        return compact('users', 'clients', 'inventory', 'contracts', 'jobOrders', 'inspections', 'payments', 'inventoryLogs', 'materials');
    }

    private function writeAll(array $payload): void
    {
        $map = [
            'users' => 'users',
            'clients' => 'clients',
            'inventory' => 'inventory_items',
            'contracts' => 'contracts',
            'jobOrders' => 'job_orders',
            'inspections' => 'site_inspections',
            'payments' => 'payments',
            'inventoryLogs' => 'inventory_logs',
            'materials' => 'job_order_materials',
        ];

        foreach ($map as $key => $table) {
            foreach ($payload[$key] as $row) {
                DB::table($table)->updateOrInsert(['id' => $row['id']], $row);
            }

            $this->counts[$table] = count($payload[$key]);
            $this->line(sprintf('  ✓ %-20s %d row(s)', $table, count($payload[$key])));
        }
    }

    private function reportPrepared(array $payload): void
    {
        $rows = [
            ['users', count($payload['users'])],
            ['clients', count($payload['clients'])],
            ['inventory_items', count($payload['inventory'])],
            ['contracts', count($payload['contracts'])],
            ['job_orders', count($payload['jobOrders'])],
            ['site_inspections', count($payload['inspections'])],
            ['payments', count($payload['payments'])],
            ['inventory_logs', count($payload['inventoryLogs'])],
            ['job_order_materials', count($payload['materials'])],
        ];

        $this->table(['Target table', 'Prepared rows'], $rows);
    }

    private function reconcile(): void
    {
        $legacy = DB::connection('legacy');

        $checks = [
            ['users', 'users'],
            ['clients', 'clients'],
            ['inventory_items', 'inventory'],
            ['contracts', 'contracts'],
            ['job_orders', 'job_orders'],
            ['site_inspections', 'site_inspections'],
            ['payments', 'payments'],
            ['inventory_logs', 'inventory_logs'],
            ['job_order_materials', 'job_order_materials'],
        ];

        $this->newLine();
        $this->info('Reconciliation');

        $rows = [];
        foreach ($checks as [$target, $source]) {
            $targetCount = DB::table($target)->count();
            $sourceCount = $legacy->table($source)->count();
            $rows[] = [$target, $sourceCount, $targetCount, $sourceCount === $targetCount ? 'OK' : 'DIFF'];
        }

        $legacyPayments = (float) $legacy->table('payments')->sum('amount_paid');
        $targetPayments = (float) DB::table('payments')->sum('amount_paid');
        $rows[] = ['payments total ₱', number_format($legacyPayments, 2), number_format($targetPayments, 2), abs($legacyPayments - $targetPayments) < 0.01 ? 'OK' : 'DIFF'];

        $legacyValue = (float) $legacy->table('contracts')->sum('contract_value');
        $targetValue = (float) DB::table('contracts')->sum('contract_value');
        $rows[] = ['contract value ₱', number_format($legacyValue, 2), number_format($targetValue, 2), abs($legacyValue - $targetValue) < 0.01 ? 'OK' : 'DIFF'];

        $this->table(['Measure', 'Legacy', 'Target', 'Result'], $rows);
    }

    private function reportWarnings(): void
    {
        if ($this->warnings === []) {
            return;
        }

        $this->newLine();
        $this->warn('Warnings');
        foreach ($this->warnings as $warning) {
            $this->line('  - ' . $warning);
        }
    }

    private function normaliseUserStatus(?string $value, int $id): string
    {
        $value = strtolower((string) $value);

        if (in_array($value, UserStatus::values(), true)) {
            return $value;
        }

        $this->warnings[] = "users#{$id}: unknown status '{$value}', defaulted to active.";

        return UserStatus::Active->value;
    }

    /**
     * @param  class-string<\BackedEnum>  $enum
     */
    private function enumOr(?string $value, string $enum, \BackedEnum $fallback, string $context): string
    {
        if (in_array($value, $enum::values(), true)) {
            return $value;
        }

        $this->warnings[] = "{$context}: unknown value '{$value}', defaulted to '{$fallback->value}'.";

        return $fallback->value;
    }

    private function technicianLookup(array $users): array
    {
        $lookup = [];

        foreach ($users as $user) {
            $lookup[$this->nameKey($user['username'])] = $user['id'];
            $lookup[$this->nameKey(trim($user['first_name'] . ' ' . $user['last_name']))] = $user['id'];
        }

        return $lookup;
    }

    private function matchTechnician(?string $name, array $lookup, string $context): ?int
    {
        if (! filled($name)) {
            return null;
        }

        $key = $this->nameKey($name);

        if (isset($lookup[$key])) {
            return $lookup[$key];
        }

        $this->warnings[] = "{$context}: technician '{$name}' not matched; left unassigned.";

        return null;
    }

    private function matchPerformer(?string $name, array $technicians, array $users, string $context): ?int
    {
        if (! filled($name)) {
            return null;
        }

        $key = $this->nameKey($name);

        if (isset($technicians[$key])) {
            return $technicians[$key];
        }

        foreach ($users as $user) {
            if ($this->nameKey($user['username']) === $key) {
                return $user['id'];
            }
        }

        // Legacy inventory logs recorded the literal "System / Admin" for automated changes.
        if (str_contains($key, 'system') || str_contains($key, 'admin')) {
            foreach ($users as $user) {
                if ($user['role'] === UserRole::Admin->value) {
                    return $user['id'];
                }
            }
        }

        $this->warnings[] = "{$context}: performed_by '{$name}' not matched; left null.";

        return null;
    }

    private function nameKey(?string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    private function dateTime(mixed $value): ?string
    {
        if (blank($value) || in_array($value, ['0000-00-00 00:00:00', '0000-00-00'], true)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function dateOnly(mixed $value): ?string
    {
        if (blank($value) || $value === '0000-00-00') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function timeOnly(mixed $value): ?string
    {
        if (blank($value) || $value === '00:00:00') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function jsonOrNull(mixed $value, string $context): ?string
    {
        if (blank($value)) {
            return null;
        }

        $decoded = json_decode((string) $value, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->warnings[] = "{$context}: invalid JSON dropped.";

            return null;
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
