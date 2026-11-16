<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
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
            'first_name' => 'Test',
            'last_name' => 'Client',
            'phone_number' => '09000000000',
            'email' => strtolower(str_replace(' ', '.', $name)) . '@vermex.test',
            'street_address' => '123 Main St',
            'barangay' => 'Matina',
            'city' => 'Davao City',
            'client_type' => 'Commercial',
            'status' => 'active',
        ]);
    }

    private function contract(Client $client, array $overrides = []): Contract
    {
        return Contract::create(array_merge([
            'client_id' => $client->id,
            'contract_name' => 'Annual Pest Control',
            'contract_status' => 'active',
            'contract_start_date' => now()->subMonth()->toDateString(),
            'contract_end_date' => now()->addYear()->toDateString(),
            'contract_value' => 10000,
        ], $overrides));
    }

    private function payment(Contract $contract, User $user, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'amount_paid' => 2000,
            'payment_type' => 'Downpayment',
            'payment_method' => 'Cash',
            'payment_date' => now(),
        ], $overrides));
    }

    public function test_admin_can_view_payment_ledger_with_live_balances(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $contract = $this->contract($this->client());
        $this->payment($contract, $admin);

        $response = $this->actingAs($admin)->get('/admin/payments')->assertOk();

        $response->assertSee('Payments &amp; Billing', false);
        $response->assertSee('First Client');
        $response->assertSee('₱2,000.00');

        $metrics = $response->viewData('metrics');
        $this->assertSame(2000.0, $metrics['collected']);
        $this->assertSame(1, $metrics['transactions']);
        $this->assertSame(10000.0, $metrics['portfolio']);
        $this->assertSame(8000.0, $metrics['outstanding']);
    }

    public function test_admin_can_record_payment_and_balance_is_computed(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $contract = $this->contract($this->client());
        $this->payment($contract, $admin);

        $this->actingAs($admin)
            ->post('/admin/payments', [
                'contract_id' => $contract->id,
                'amount_paid' => 3000,
                'payment_type' => 'Balance Settlement',
                'payment_method' => 'GCash',
                'reference_number' => 'OR-1001',
                'remarks' => 'Partial settlement',
                'payment_date' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect('/admin/payments');

        $contract->refresh();

        $this->assertSame(5000.0, $contract->paid_amount);
        $this->assertSame(5000.0, $contract->final_balance);

        $payment = Payment::where('reference_number', 'OR-1001')->firstOrFail();
        $this->assertSame($admin->id, $payment->user_id);
        $this->assertSame('GCash', $payment->payment_method->value);

        $this->assertFalse(Schema::hasColumn('contracts', 'final_balance'));
    }

    public function test_billing_officer_can_record_payment(): void
    {
        $billing = $this->user(UserRole::BillingOfficer, 'billing');
        $contract = $this->contract($this->client());

        $this->actingAs($billing)
            ->post('/billing/payments', [
                'contract_id' => $contract->id,
                'amount_paid' => 2500,
                'payment_type' => 'Downpayment',
                'payment_method' => 'Bank Transfer',
                'payment_date' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect('/billing/payments');

        $this->assertSame(2500.0, $contract->fresh()->paid_amount);
        $this->assertSame(7500.0, $contract->fresh()->final_balance);
    }

    public function test_overpayment_is_rejected(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $contract = $this->contract($this->client());

        $this->actingAs($admin)
            ->post('/admin/payments', [
                'contract_id' => $contract->id,
                'amount_paid' => 15000,
                'payment_type' => 'Full Payment',
                'payment_method' => 'Cash',
                'payment_date' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('amount_paid');

        $this->assertSame(0, Payment::count());
        $this->assertSame(0.0, $contract->fresh()->paid_amount);
    }

    public function test_archived_contract_cannot_receive_payment(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $contract = $this->contract($this->client(), ['contract_status' => 'archived']);

        $this->actingAs($admin)
            ->post('/admin/payments', [
                'contract_id' => $contract->id,
                'amount_paid' => 1000,
                'payment_type' => 'Downpayment',
                'payment_method' => 'Cash',
                'payment_date' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('contract_id');

        $this->assertSame(0, Payment::count());
    }

    public function test_payment_filters_search_and_date_range(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $first = $this->contract($this->client('First Client'));
        $second = $this->contract($this->client('Second Client'));

        $this->payment($first, $admin, ['payment_date' => now()->subMonths(2)]);
        $this->payment($second, $admin, ['payment_date' => now()]);

        $search = $this->actingAs($admin)
            ->get('/admin/payments?search=Second')
            ->assertOk();

        $this->assertSame(1, $search->viewData('payments')->total());
        $this->assertSame('Second Client', $search->viewData('payments')->first()->contract->client->client_name);

        $month = $this->actingAs($admin)
            ->get('/admin/payments?date_filter=this-month')
            ->assertOk();

        $this->assertSame(1, $month->viewData('payments')->total());
        $this->assertSame(2000.0, $month->viewData('metrics')['collected']);
    }

    public function test_operations_roles_cannot_access_payment_ledgers(): void
    {
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $custodian = $this->user(UserRole::ChemicalCustodian, 'custodian');

        $this->actingAs($technician)->get('/admin/payments')->assertForbidden();
        $this->actingAs($technician)->get('/billing/payments')->assertForbidden();
        $this->actingAs($custodian)->get('/admin/payments')->assertForbidden();
        $this->actingAs($custodian)->get('/billing/payments')->assertForbidden();
    }
}
