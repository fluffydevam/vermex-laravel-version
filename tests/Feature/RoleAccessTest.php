<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function user(UserRole $role, UserStatus $status = UserStatus::Active): User
    {
        $this->sequence++;

        return User::create([
            'username' => 'user' . $this->sequence,
            'email' => "user{$this->sequence}@vermex.test",
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => $role->value,
            'role' => $role->value,
            'status' => $status->value,
        ]);
    }

    public function test_guest_is_sent_to_login_from_protected_pages(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/clients')->assertRedirect('/login');
        $this->get('/billing/dashboard')->assertRedirect('/login');
        $this->get('/field/dashboard')->assertRedirect('/login');
        $this->get('/custodian/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_open_operational_dashboards(): void
    {
        $admin = $this->user(UserRole::Admin);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/clients')->assertOk();
        $this->actingAs($admin)->get('/billing/dashboard')->assertOk();
        $this->actingAs($admin)->get('/field/dashboard')->assertOk();
        $this->actingAs($admin)->get('/custodian/dashboard')->assertOk();
    }

    public function test_billing_officer_can_access_client_accounts_and_billing_only(): void
    {
        $billing = $this->user(UserRole::BillingOfficer);

        $this->actingAs($billing)->get('/billing/dashboard')->assertOk();
        $this->actingAs($billing)->get('/billing/accounts')->assertOk();

        $this->actingAs($billing)->get('/admin/clients')->assertForbidden();
        $this->actingAs($billing)->get('/admin/clients/create')->assertForbidden();
        $this->actingAs($billing)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($billing)->get('/admin/inventory')->assertForbidden();
        $this->actingAs($billing)->get('/admin/job-orders')->assertForbidden();
        $this->actingAs($billing)->get('/field/dashboard')->assertForbidden();
        $this->actingAs($billing)->get('/custodian/dashboard')->assertForbidden();
    }

    public function test_field_technician_can_access_field_only(): void
    {
        $technician = $this->user(UserRole::FieldTechnician);

        $this->actingAs($technician)->get('/field/dashboard')->assertOk();

        $this->actingAs($technician)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($technician)->get('/admin/clients')->assertForbidden();
        $this->actingAs($technician)->get('/admin/inventory')->assertForbidden();
        $this->actingAs($technician)->get('/admin/job-orders')->assertForbidden();
        $this->actingAs($technician)->get('/billing/dashboard')->assertForbidden();
        $this->actingAs($technician)->get('/custodian/dashboard')->assertForbidden();
    }

    public function test_chemical_custodian_can_access_inventory_only(): void
    {
        $custodian = $this->user(UserRole::ChemicalCustodian);

        $this->actingAs($custodian)->get('/custodian/dashboard')->assertOk();

        $this->actingAs($custodian)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($custodian)->get('/admin/clients')->assertForbidden();
        $this->actingAs($custodian)->get('/admin/inventory')->assertForbidden();
        $this->actingAs($custodian)->get('/admin/job-orders')->assertForbidden();
        $this->actingAs($custodian)->get('/billing/dashboard')->assertForbidden();
        $this->actingAs($custodian)->get('/field/dashboard')->assertForbidden();
    }

    public function test_logout_is_post_only_and_sidebar_submits_post_form(): void
    {
        $admin = $this->user(UserRole::Admin);

        $this->get('/logout')->assertStatus(405);

        $response = $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $response->assertSee('action="' . route('logout') . '"', false);
        $response->assertSee('method="POST"', false);
        $response->assertSee('Logout');

        $this->actingAs($admin)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_disabled_user_is_logged_out_and_redirected(): void
    {
        $disabled = $this->user(UserRole::Admin, UserStatus::Disabled);

        $response = $this->actingAs($disabled)->get('/admin/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
        $this->assertGuest();
    }
}
