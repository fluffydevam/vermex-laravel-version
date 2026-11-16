<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Enums\ContractStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAccountTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function user(UserRole $role): User
    {
        $this->sequence++;

        return User::create([
            'username' => 'billinguser' . $this->sequence,
            'email' => "billinguser{$this->sequence}@vermex.test",
            'password' => 'password',
            'first_name' => 'Billing',
            'last_name' => 'Officer',
            'role' => $role->value,
            'status' => 'active',
        ]);
    }

    private function client(string $name = 'Billing Client'): Client
    {
        return Client::create([
            'client_name' => $name,
            'first_name' => 'Billing',
            'last_name' => 'Client',
            'phone_number' => '+63 900 000 0100',
            'street_address' => '100 Billing St',
            'barangay' => 'Billing Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Commercial->value,
            'status' => ClientStatus::Active->value,
        ]);
    }

    private function contract(Client $client, string $name = 'Billing Contract'): Contract
    {
        return $client->contracts()->create([
            'contract_name' => $name,
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addYear(),
            'contract_value' => 5000,
            'contract_status' => ContractStatus::Active->value,
        ]);
    }

    public function test_billing_officer_sees_read_only_client_accounts_list(): void
    {
        $billing = $this->user(UserRole::BillingOfficer);
        $client = $this->client('Read Only Client');
        $contract = $this->contract($client);

        $this->actingAs($billing)
            ->get('/billing/accounts')
            ->assertOk()
            ->assertViewIs('admin.clients.index')
            ->assertSee('Read Only Client')
            ->assertSee(route('billing.accounts.show', $client), false)
            ->assertDontSee('Add Client')
            ->assertDontSee('Edit')
            ->assertDontSee(route('admin.clients.edit', $client), false)
            ->assertDontSee(route('admin.clients.archive', $client), false)
            ->assertDontSee(route('admin.clients.show', $client), false);

        $this->actingAs($billing)
            ->get('/billing/accounts?tab=contracts')
            ->assertOk()
            ->assertSee($contract->contract_name)
            ->assertDontSee(route('admin.contracts.archive', $contract), false)
            ->assertDontSee(route('admin.contracts.restore', $contract), false);
    }

    public function test_billing_officer_sees_client_detail_read_only(): void
    {
        $billing = $this->user(UserRole::BillingOfficer);
        $client = $this->client('Detail Billing Client');
        $contract = $this->contract($client, 'Detail Billing Contract');

        $contract->payments()->create([
            'user_id' => $billing->id,
            'payment_date' => now(),
            'amount_paid' => 2000,
            'payment_type' => PaymentType::Downpayment->value,
            'payment_method' => PaymentMethod::Cash->value,
        ]);

        $this->actingAs($billing)
            ->get(route('billing.accounts.show', $client))
            ->assertOk()
            ->assertViewIs('admin.clients.show')
            ->assertSee('Detail Billing Client')
            ->assertSee('Detail Billing Contract')
            ->assertSee('₱2,000.00')
            ->assertSee(route('billing.accounts'), false)
            ->assertDontSee('Edit client')
            ->assertDontSee('Add contract')
            ->assertDontSee(route('admin.clients.edit', $client), false)
            ->assertDontSee(route('admin.clients.archive', $client), false)
            ->assertDontSee(route('admin.clients.restore', $client), false)
            ->assertDontSee(route('admin.contracts.store'), false);
    }

    public function test_billing_officer_cannot_use_admin_client_or_contract_writes(): void
    {
        $billing = $this->user(UserRole::BillingOfficer);
        $client = $this->client();
        $contract = $this->contract($client);

        $this->actingAs($billing)->get('/admin/clients')->assertForbidden();
        $this->actingAs($billing)->get('/admin/clients/create')->assertForbidden();
        $this->actingAs($billing)->post('/admin/clients', [])->assertForbidden();
        $this->actingAs($billing)->get(route('admin.clients.show', $client))->assertForbidden();
        $this->actingAs($billing)->get(route('admin.clients.edit', $client))->assertForbidden();
        $this->actingAs($billing)->put(route('admin.clients.update', $client), [])->assertForbidden();
        $this->actingAs($billing)->delete(route('admin.clients.archive', $client))->assertForbidden();
        $this->actingAs($billing)->put(route('admin.clients.restore', $client))->assertForbidden();

        $this->actingAs($billing)->post(route('admin.contracts.store'), [])->assertForbidden();
        $this->actingAs($billing)->put(route('admin.contracts.update', $contract), [])->assertForbidden();
        $this->actingAs($billing)->put(route('admin.contracts.archive', $contract))->assertForbidden();
        $this->actingAs($billing)->put(route('admin.contracts.restore', $contract))->assertForbidden();
        $this->actingAs($billing)->delete(route('admin.contracts.destroy', $contract))->assertForbidden();
    }

    public function test_admin_client_pages_still_show_management_actions(): void
    {
        $admin = $this->user(UserRole::Admin);
        $client = $this->client('Admin Managed Client');
        $contract = $this->contract($client, 'Admin Managed Contract');

        $this->actingAs($admin)
            ->get('/billing/accounts')
            ->assertOk()
            ->assertSee('Add Client')
            ->assertSee(route('admin.clients.edit', $client), false)
            ->assertSee(route('admin.clients.archive', $client), false);

        $this->actingAs($admin)
            ->get(route('billing.accounts.show', $client))
            ->assertOk()
            ->assertSee('Edit client')
            ->assertSee('Add contract')
            ->assertSee(route('admin.contracts.store'), false);

        $this->actingAs($admin)
            ->get('/billing/accounts?tab=contracts')
            ->assertOk()
            ->assertSee(route('admin.contracts.archive', $contract), false);
    }
}
