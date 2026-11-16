<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contract;
use App\Models\InventoryItem;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportManagementTest extends TestCase
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
            'sector_region' => 'Davao',
        ]);
    }

    private function client(): Client
    {
        return Client::create([
            'client_name' => 'Report Client',
            'first_name' => 'Test',
            'last_name' => 'Client',
            'phone_number' => '09000000000',
            'street_address' => '123 Main St',
            'barangay' => 'Matina',
            'city' => 'Davao City',
            'client_type' => 'Commercial',
            'status' => 'active',
        ]);
    }

    private function contract(Client $client): Contract
    {
        return Contract::create([
            'client_id' => $client->id,
            'contract_name' => 'Report Contract',
            'contract_status' => 'active',
            'contract_start_date' => now()->subMonth()->toDateString(),
            'contract_end_date' => now()->addYear()->toDateString(),
            'contract_value' => 10000,
        ]);
    }

    private function payment(Contract $contract, User $user, float $amount, $date): Payment
    {
        return Payment::create([
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'amount_paid' => $amount,
            'payment_type' => 'Downpayment',
            'payment_method' => 'Cash',
            'payment_date' => $date,
        ]);
    }

    public function test_admin_report_contains_financial_and_operations_metrics(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $client = $this->client();
        $contract = $this->contract($client);
        $this->payment($contract, $admin, 2500, now());

        $job = JobOrder::create([
            'client_id' => $client->id,
            'technician_id' => $technician->id,
            'client_name' => $client->client_name,
            'service_address' => $client->address,
            'service_type' => 'General pest control',
            'scheduled_date' => today(),
            'service_window' => '09:00–10:30',
            'priority' => 'Standard',
            'route_status' => 'Completed',
        ]);

        $item = InventoryItem::create([
            'item_name' => 'Baygon',
            'category' => 'Chemical',
            'quantity_in_stock' => 10,
            'unit' => 'Liters',
            'min_threshold' => 2,
            'unit_cost' => 100,
        ]);

        DB::table('job_order_materials')->insert([
            'job_order_id' => $job->id,
            'inventory_item_id' => $item->id,
            'quantity_used' => 3,
            'unit_used' => 'Liters',
            'logged_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports')->assertOk();

        $response->assertSee('Reports & Analytics');
        $response->assertSee('Field technician productivity');

        $financial = $response->viewData('financial');
        $this->assertSame(2500.0, $financial['revenue']);
        $this->assertSame(10000.0, $financial['portfolio']);
        $this->assertSame(7500.0, $financial['outstanding']);

        $operations = $response->viewData('operations');
        $this->assertSame(1, $operations['jobs_total']);
        $this->assertSame(1, $operations['jobs_completed']);
        $this->assertSame(100.0, $operations['fulfillment_rate']);
        $this->assertSame(3.0, $operations['chemical_quantity']);
        $this->assertSame(300.0, $operations['chemical_cost']);
    }

    public function test_billing_report_is_financial_only(): void
    {
        $billing = $this->user(UserRole::BillingOfficer, 'billing');
        $contract = $this->contract($this->client());
        $this->payment($contract, $billing, 1500, now());

        $response = $this->actingAs($billing)->get('/billing/reports')->assertOk();

        $response->assertSee('Financial Reports');
        $response->assertDontSee('Field technician productivity');

        $this->assertFalse($response->viewData('showOperations'));
        $this->assertNull($response->viewData('operations'));
        $this->assertSame(1500.0, $response->viewData('financial')['revenue']);
    }

    public function test_report_period_filter_scopes_revenue(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $contract = $this->contract($this->client());

        $this->payment($contract, $admin, 500, now());
        $this->payment($contract, $admin, 1000, now()->subMonthNoOverflow());

        $thisMonth = $this->actingAs($admin)->get('/admin/reports?filter=this-month')->assertOk();
        $this->assertSame(500.0, $thisMonth->viewData('financial')['revenue']);

        $lastMonth = $this->actingAs($admin)->get('/admin/reports?filter=last-month')->assertOk();
        $this->assertSame(1000.0, $lastMonth->viewData('financial')['revenue']);

        $custom = $this->actingAs($admin)->get('/admin/reports?' . http_build_query([
            'filter' => 'custom',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]))->assertOk();
        $this->assertSame(500.0, $custom->viewData('financial')['revenue']);
    }

    public function test_operations_roles_cannot_view_reports(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $custodian = $this->user(UserRole::ChemicalCustodian, 'custodian');

        $this->actingAs($technician)->get('/admin/reports')->assertForbidden();
        $this->actingAs($technician)->get('/billing/reports')->assertForbidden();
        $this->actingAs($custodian)->get('/admin/reports')->assertForbidden();
        $this->actingAs($custodian)->get('/billing/reports')->assertForbidden();
    }
}
