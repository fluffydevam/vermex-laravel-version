<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contract;
use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role, string $username): User
    {
        return User::create([
            'username' => $username,
            'email' => "{$username}@vermex.test",
            'password' => 'password',
            'first_name' => ucfirst($username),
            'last_name' => 'Tester',
            'role' => $role->value,
            'status' => 'active',
        ]);
    }

    private function client(): Client
    {
        return Client::create([
            'client_name' => 'First Client',
            'first_name' => 'Andrey',
            'last_name' => 'Pogi',
            'phone_number' => '09674314142',
            'email' => 'client@vermex.test',
            'street_address' => 'Maa, Matina',
            'barangay' => 'Matina',
            'city' => 'Davao City',
            'client_type' => 'Commercial',
            'status' => 'active',
        ]);
    }

    private function job(User $technician, array $overrides = []): JobOrder
    {
        $client = $this->client();

        return JobOrder::create(array_merge([
            'client_id' => $client->id,
            'technician_id' => $technician->id,
            'client_name' => $client->client_name,
            'service_address' => $client->address,
            'service_type' => 'General pest control',
            'scheduled_date' => today(),
            'service_window' => '09:00–10:30',
            'priority' => 'Standard',
            'route_status' => 'Scheduled',
        ], $overrides));
    }

    public function test_admin_can_schedule_job_order_with_snapshots(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $client = $this->client();

        $contract = Contract::create([
            'client_id' => $client->id,
            'contract_name' => 'Annual Pest Control',
            'contract_status' => 'active',
            'contract_start_date' => now()->toDateString(),
            'contract_end_date' => now()->addYear()->toDateString(),
            'contract_value' => 25000,
        ]);

        $this->actingAs($admin)
            ->post('/admin/job-orders', [
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'technician_id' => $technician->id,
                'service_type' => 'General pest control',
                'scheduled_date' => now()->addDay()->toDateString(),
                'service_window' => '09:00–10:30',
                'priority' => 'High',
                'comments' => 'Gate code 1234',
                'area_findings' => "Kitchen\nStorage room",
            ])
            ->assertRedirect('/admin/job-orders');

        $job = JobOrder::firstOrFail();

        $this->assertSame('First Client', $job->client_name);
        $this->assertSame('Maa, Matina, Matina, Davao City', $job->service_address);
        $this->assertSame('Scheduled', $job->route_status->value);
        $this->assertSame($technician->id, $job->technician_id);
        $this->assertSame(['Kitchen', 'Storage room'], collect($job->area_findings)->pluck('area')->all());
    }

    public function test_job_order_rejects_contract_from_another_client(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $client = $this->client();

        $other = Client::create([
            'client_name' => 'Other Client',
            'first_name' => 'Other',
            'last_name' => 'Client',
            'phone_number' => '09000000000',
            'street_address' => 'Other St',
            'barangay' => 'Other',
            'city' => 'Davao City',
            'client_type' => 'Residential',
            'status' => 'active',
        ]);

        $contract = Contract::create([
            'client_id' => $other->id,
            'contract_name' => 'Other Contract',
            'contract_status' => 'active',
            'contract_value' => 1000,
        ]);

        $this->actingAs($admin)
            ->post('/admin/job-orders', [
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'service_type' => 'General pest control',
                'scheduled_date' => now()->toDateString(),
                'service_window' => '09:00–10:30',
                'priority' => 'Standard',
            ])
            ->assertSessionHasErrors('contract_id');

        $this->assertSame(0, JobOrder::count());
    }

    public function test_technician_can_only_open_their_own_job(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $otherTech = $this->user(UserRole::FieldTechnician, 'othertech');

        $ownJob = $this->job($technician);
        $otherJob = $this->job($otherTech);

        $this->actingAs($technician)->get("/field/job-orders/{$ownJob->id}")->assertOk();
        $this->actingAs($technician)->get("/field/job-orders/{$otherJob->id}")->assertForbidden();
    }

    public function test_status_update_stamps_time_in_and_time_out(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $job = $this->job($technician);

        $this->actingAs($technician)
            ->post("/field/job-orders/{$job->id}/status", ['route_status' => 'En route'])
            ->assertRedirect();

        $this->assertNotNull($job->fresh()->time_in);
        $this->assertNull($job->fresh()->time_out);

        $this->actingAs($technician)
            ->post("/field/job-orders/{$job->id}/status", ['route_status' => 'Completed'])
            ->assertRedirect();

        $fresh = $job->fresh();
        $this->assertSame('Completed', $fresh->route_status->value);
        $this->assertNotNull($fresh->time_out);
    }

    public function test_logging_materials_deducts_stock_and_writes_audit_rows(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $job = $this->job($technician, ['route_status' => 'On site']);

        $item = InventoryItem::create([
            'item_name' => 'Baygon',
            'category' => 'Chemical',
            'quantity_in_stock' => 50,
            'unit' => 'Liters',
            'min_threshold' => 10,
            'unit_cost' => 25,
        ]);

        $this->actingAs($technician)
            ->post("/field/job-orders/{$job->id}/materials", [
                'inventory_item_id' => $item->id,
                'quantity_used' => 5,
                'remarks' => 'Kitchen perimeter',
            ])
            ->assertRedirect();

        $this->assertSame('45.00', $item->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('job_order_materials', [
            'job_order_id' => $job->id,
            'inventory_item_id' => $item->id,
            'quantity_used' => 5,
            'unit_used' => 'Liters',
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_item_id' => $item->id,
            'job_order_id' => $job->id,
            'performed_by' => $technician->id,
            'action_type' => 'Job Order Deduction',
            'quantity_changed' => -5,
        ]);
    }

    public function test_material_usage_above_stock_is_rejected(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $job = $this->job($technician, ['route_status' => 'On site']);

        $item = InventoryItem::create([
            'item_name' => 'Rat Bait',
            'category' => 'Chemical',
            'quantity_in_stock' => 2,
            'unit' => 'Kg',
            'min_threshold' => 1,
            'unit_cost' => 100,
        ]);

        $this->actingAs($technician)
            ->post("/field/job-orders/{$job->id}/materials", [
                'inventory_item_id' => $item->id,
                'quantity_used' => 5,
            ])
            ->assertSessionHasErrors('quantity_used');

        $this->assertSame('2.00', $item->fresh()->quantity_in_stock);
        $this->assertDatabaseCount('job_order_materials', 0);
    }

    public function test_materials_cannot_be_logged_before_job_starts(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $job = $this->job($technician);

        $item = InventoryItem::create([
            'item_name' => 'Glue Trap',
            'category' => 'Device/Trap',
            'quantity_in_stock' => 10,
            'unit' => 'Pcs',
            'min_threshold' => 2,
            'unit_cost' => 15,
        ]);

        $this->actingAs($technician)
            ->post("/field/job-orders/{$job->id}/materials", [
                'inventory_item_id' => $item->id,
                'quantity_used' => 1,
            ])
            ->assertSessionHas('error');

        $this->assertSame('10.00', $item->fresh()->quantity_in_stock);
    }
}
