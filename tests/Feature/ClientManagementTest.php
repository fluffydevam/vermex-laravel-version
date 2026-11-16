<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Enums\ClientStatus;
use App\Enums\ClientType;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_clients_page(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/clients');

        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.index');
    }

    public function test_admin_can_create_client(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->post('/admin/clients', [
            'client_name' => 'Deca Homes',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'client_type' => 'Residential',
            'email' => 'juan@decahomes.com',
            'phone_number' => '+63 912 345 6789',
            'street_address' => 'Block 12 Lot 3',
            'barangay' => 'Indangan',
            'city' => 'Davao City',
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/clients?tab=clients');
        $this->assertDatabaseHas('clients', [
            'client_name' => 'Deca Homes',
            'email' => 'juan@decahomes.com',
        ]);
    }

    public function test_admin_can_view_client_detail_with_contracts_and_payments(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $client = Client::create([
            'client_name' => 'Detail Client',
            'first_name' => 'Detail',
            'last_name' => 'Client',
            'phone_number' => '+63 900 000 0002',
            'street_address' => '123 Detail St',
            'barangay' => 'Detail Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Commercial->value,
            'status' => ClientStatus::Active->value,
        ]);

        $contract = $client->contracts()->create([
            'contract_name' => 'Detail Contract',
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addYear(),
            'contract_value' => 5000,
            'contract_status' => \App\Enums\ContractStatus::Active->value,
        ]);

        $contract->payments()->create([
            'user_id' => $admin->id,
            'payment_date' => now(),
            'amount_paid' => 2000,
            'payment_type' => \App\Enums\PaymentType::Downpayment->value,
            'payment_method' => \App\Enums\PaymentMethod::Cash->value,
        ]);

        $this->actingAs($admin)
            ->get('/admin/clients/' . $client->id)
            ->assertOk()
            ->assertSee('Detail Client')
            ->assertSee('Detail Contract')
            ->assertSee('₱2,000.00');
    }

    public function test_admin_can_update_client(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $client = Client::create([
            'client_name' => 'Test Client',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone_number' => '+63 900 000 0000',
            'street_address' => '123 Test St',
            'barangay' => 'Test Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Residential->value,
            'status' => ClientStatus::Active->value,
        ]);

        $response = $this->actingAs($admin)->put('/admin/clients/' . $client->id, [
            'client_name' => 'Updated Client',
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'client_type' => 'Commercial',
            'email' => $client->email,
            'phone_number' => '+63 900 000 0000',
            'street_address' => '456 Updated St',
            'barangay' => 'Updated Brgy',
            'city' => 'Davao City',
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin/clients?tab=clients');
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'client_name' => 'Updated Client',
        ]);
    }

    public function test_admin_can_archive_client(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $client = Client::create([
            'client_name' => 'Archive Test',
            'first_name' => 'Archive',
            'last_name' => 'Test',
            'phone_number' => '+63 900 000 0000',
            'street_address' => '123 Test St',
            'barangay' => 'Test Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Residential->value,
            'status' => ClientStatus::Active->value,
        ]);

        $contract = $client->contracts()->create([
            'contract_name' => 'Annual Pest Control',
            'contract_start_date' => now()->subMonth(),
            'contract_end_date' => now()->addYear(),
            'contract_value' => 1000,
            'contract_status' => \App\Enums\ContractStatus::Active->value,
        ]);

        $contract->payments()->create([
            'user_id' => $admin->id,
            'payment_date' => now(),
            'amount_paid' => 1000,
            'payment_type' => \App\Enums\PaymentType::FullPayment->value,
            'payment_method' => \App\Enums\PaymentMethod::Cash->value,
        ]);

        $response = $this->actingAs($admin)->delete('/admin/clients/' . $client->id);

        $response->assertRedirect('/admin/clients?tab=clients');
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'status' => ClientStatus::Archived->value,
        ]);
    }

    public function test_client_without_contracts_is_deleted_on_archive(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $client = Client::create([
            'client_name' => 'No Contract Client',
            'first_name' => 'No',
            'last_name' => 'Contract',
            'phone_number' => '+63 900 000 0001',
            'street_address' => '123 Test St',
            'barangay' => 'Test Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Residential->value,
            'status' => ClientStatus::Active->value,
        ]);

        $this->actingAs($admin)
            ->delete('/admin/clients/' . $client->id)
            ->assertRedirect('/admin/clients?tab=clients');

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_clients_list_shows_view_and_edit_actions(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $client = Client::create([
            'client_name' => 'Action Client',
            'first_name' => 'Action',
            'last_name' => 'Client',
            'phone_number' => '+63 900 000 0003',
            'street_address' => '123 Action St',
            'barangay' => 'Action Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Residential->value,
            'status' => ClientStatus::Active->value,
        ]);

        $html = $this->actingAs($admin)->get('/admin/clients')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<a href="' . preg_quote(route('admin.clients.show', $client), '/') . '"[^>]*>\s*View\s*<\/a>/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<a href="' . preg_quote(route('admin.clients.edit', $client), '/') . '"[^>]*>\s*Edit\s*<\/a>/',
            $html
        );
    }

    public function test_edit_client_cancel_returns_to_clients_list(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->first();

        $client = Client::create([
            'client_name' => 'Cancel Client',
            'first_name' => 'Cancel',
            'last_name' => 'Client',
            'phone_number' => '+63 900 000 0004',
            'street_address' => '123 Cancel St',
            'barangay' => 'Cancel Brgy',
            'city' => 'Davao City',
            'client_type' => ClientType::Residential->value,
            'status' => ClientStatus::Active->value,
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.clients.edit', $client))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<a href="' . preg_quote(route('admin.clients'), '/') . '"[^>]*>\s*Cancel\s*<\/a>/',
            $html
        );
    }
}
