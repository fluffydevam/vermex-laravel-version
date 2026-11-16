<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contract;
use App\Models\SiteInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionManagementTest extends TestCase
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

    private function client(string $name = 'First Client'): Client
    {
        return Client::create([
            'client_name' => $name,
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

    private function inspection(User $technician, array $overrides = []): SiteInspection
    {
        $client = $this->client();

        return SiteInspection::create(array_merge([
            'client_id' => $client->id,
            'technician_id' => $technician->id,
            'client_name' => $client->client_name,
            'client_email' => $client->email,
            'service_address' => $client->address,
            'account_type' => 'Commercial',
            'inspection_status' => 'Pending Visit',
            'inspection_date' => today(),
            'conditions_json' => [],
            'findings_json' => [],
        ], $overrides));
    }

    public function test_admin_can_create_inspection_with_conditions_and_findings(): void
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
            ->post('/admin/inspections', [
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'technician_id' => $technician->id,
                'inspection_status' => 'Inspection Complete (Pests Found)',
                'inspection_date' => now()->toDateString(),
                'time_in' => '09:15',
                'time_out' => '10:45',
                'vermex_representative' => 'John Mark',
                'client_representative' => 'Andrey Pogi',
                'ilt_qty' => 4,
                'ilt_remarks' => 'Two replaced',
                'rat_cage_qty' => 2,
                'rat_bait_qty' => 1,
                'glue_trap_qty' => 3,
                'conditions' => [
                    0 => ['status' => 'yes', 'area' => 'Kitchen'],
                    1 => ['area' => 'Storage'],
                ],
                'findings' => [
                    ['area' => 'Kitchen', 'findings' => 'Cockroaches', 'action_taken' => 'Gel bait', 'remarks' => 'Follow up'],
                ],
            ])
            ->assertRedirect();

        $inspection = SiteInspection::firstOrFail();

        $this->assertSame('First Client', $inspection->client_name);
        $this->assertSame('Maa, Matina, Matina, Davao City', $inspection->service_address);
        $this->assertSame($technician->id, $inspection->technician_id);
        $this->assertSame('09:15:00', $inspection->time_in);
        $this->assertSame('10:45:00', $inspection->time_out);
        $this->assertTrue($inspection->pests_found);

        $this->assertSame('Prolonged Open Door - Entry Point of Pests', $inspection->conditions_json[0]['label']);
        $this->assertSame('yes', $inspection->conditions_json[0]['status']);
        $this->assertSame('Kitchen', $inspection->conditions_json[0]['area']);
        $this->assertSame('Open Garbage Bin - Attracts Rodents & Flies', $inspection->conditions_json[1]['label']);
        $this->assertSame('', $inspection->conditions_json[1]['status']);
        $this->assertSame('Cockroaches', $inspection->findings_json[0]['findings']);
    }

    public function test_inspection_rejects_contract_from_another_client(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $client = $this->client();
        $other = $this->client('Other Client');

        $contract = Contract::create([
            'client_id' => $other->id,
            'contract_name' => 'Other Contract',
            'contract_status' => 'active',
            'contract_value' => 1000,
        ]);

        $this->actingAs($admin)
            ->post('/admin/inspections', [
                'client_id' => $client->id,
                'contract_id' => $contract->id,
                'inspection_status' => 'Pending Visit',
            ])
            ->assertSessionHasErrors('contract_id');

        $this->assertSame(0, SiteInspection::count());
    }

    public function test_field_technician_can_update_own_inspection(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $other = $this->user(UserRole::FieldTechnician, 'othertech');

        $inspection = $this->inspection($technician);

        $this->actingAs($technician)
            ->put("/field/inspections/{$inspection->id}", [
                'client_id' => $inspection->client_id,
                'technician_id' => $other->id,
                'inspection_status' => 'Inspection Complete (Pests Found)',
                'inspection_date' => now()->toDateString(),
                'findings' => [
                    ['area' => 'Pantry', 'findings' => 'Rat droppings', 'action_taken' => 'Traps set', 'remarks' => ''],
                ],
            ])
            ->assertRedirect();

        $fresh = $inspection->fresh();

        // Technician cannot reassign the inspection away from themselves.
        $this->assertSame($technician->id, $fresh->technician_id);
        $this->assertTrue($fresh->pests_found);
        $this->assertSame('Rat droppings', $fresh->findings_json[0]['findings']);
    }

    public function test_technician_cannot_open_or_update_another_technicians_inspection(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $other = $this->user(UserRole::FieldTechnician, 'othertech');

        $inspection = $this->inspection($other);

        $this->actingAs($technician)->get("/field/inspections/{$inspection->id}")->assertForbidden();

        $this->actingAs($technician)
            ->put("/field/inspections/{$inspection->id}", [
                'client_id' => $inspection->client_id,
                'inspection_status' => 'Inspection Complete',
            ])
            ->assertForbidden();
    }

    public function test_field_index_only_lists_own_inspections(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $other = $this->user(UserRole::FieldTechnician, 'othertech');

        $this->inspection($technician, ['client_name' => 'Mine Client']);
        $this->inspection($other, ['client_name' => 'Theirs Client']);

        $this->actingAs($technician)
            ->get('/field/inspections')
            ->assertOk()
            ->assertSee('Mine Client')
            ->assertDontSee('Theirs Client');
    }

    public function test_billing_officer_cannot_access_inspections(): void
    {
        $billing = $this->user(UserRole::BillingOfficer, 'billing');

        $this->actingAs($billing)->get('/admin/inspections')->assertForbidden();
        $this->actingAs($billing)->get('/field/inspections')->assertForbidden();
    }
}
