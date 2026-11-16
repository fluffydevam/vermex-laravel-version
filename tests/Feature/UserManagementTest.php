<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role, string $username, array $overrides = []): User
    {
        return User::create(array_merge([
            'username' => $username,
            'email' => "{$username}@vermex.test",
            'password' => 'password',
            'first_name' => ucfirst($username),
            'last_name' => 'Tester',
            'role' => $role->value,
            'status' => UserStatus::Active->value,
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'New',
            'last_name' => 'Technician',
            'username' => 'newtech',
            'email' => 'newtech@vermex.test',
            'password' => 'Secret123!',
            'phone' => '09170000000',
            'role' => UserRole::FieldTechnician->value,
            'sector_region' => 'Davao City',
            'status' => UserStatus::Active->value,
        ], $overrides);
    }

    public function test_admin_can_view_user_directory_with_filters(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $this->user(UserRole::FieldTechnician, 'techrone');
        $this->user(UserRole::BillingOfficer, 'billingone');

        $response = $this->actingAs($admin)->get('/admin/users')->assertOk();

        $response->assertSee('Users');
        $response->assertSee('techrone');
        $response->assertSee('billingone');

        $filtered = $this->actingAs($admin)
            ->get('/admin/users?role=' . urlencode(UserRole::BillingOfficer->value))
            ->assertOk();

        $filtered->assertSee('billingone');
        $filtered->assertDontSee('techrone');
        $this->assertSame(1, $filtered->viewData('users')->total());
    }

    public function test_admin_can_create_user_with_hashed_password(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');

        $this->actingAs($admin)
            ->post('/admin/users', $this->payload())
            ->assertRedirect('/admin/users');

        $created = User::where('username', 'newtech')->firstOrFail();

        $this->assertSame(UserRole::FieldTechnician, $created->role);
        $this->assertSame(UserStatus::Active, $created->status);
        $this->assertTrue(Hash::check('Secret123!', $created->password));
    }

    public function test_create_user_validates_unique_username_and_email(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $this->user(UserRole::FieldTechnician, 'taken');

        $this->actingAs($admin)
            ->post('/admin/users', $this->payload(['username' => 'taken', 'email' => 'taken@vermex.test']))
            ->assertSessionHasErrors(['username', 'email']);

        $this->assertSame(2, User::count());
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $target = $this->user(UserRole::FieldTechnician, 'target');
        $originalHash = $target->password;

        $this->actingAs($admin)
            ->put("/admin/users/{$target->id}", $this->payload([
                'username' => 'target',
                'email' => 'target@vermex.test',
                'first_name' => 'Updated',
                'password' => '',
                'role' => UserRole::ChemicalCustodian->value,
            ]))
            ->assertRedirect('/admin/users');

        $fresh = $target->fresh();

        $this->assertSame('Updated', $fresh->first_name);
        $this->assertSame(UserRole::ChemicalCustodian, $fresh->role);
        $this->assertSame($originalHash, $fresh->password);
    }

    public function test_admin_cannot_disable_own_account(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');

        $this->actingAs($admin)
            ->from('/admin/users')
            ->post("/admin/users/{$admin->id}/toggle-status")
            ->assertRedirect('/admin/users')
            ->assertSessionHas('error');

        $this->assertSame(UserStatus::Active, $admin->fresh()->status);
    }

    public function test_last_active_admin_cannot_demote_themselves(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');

        $this->actingAs($admin)
            ->from("/admin/users/{$admin->id}/edit")
            ->put("/admin/users/{$admin->id}", $this->payload([
                'username' => 'admin',
                'email' => 'admin@vermex.test',
                'password' => '',
                'role' => UserRole::BillingOfficer->value,
            ]))
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_admin_can_demote_another_admin_when_one_remains(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $second = $this->user(UserRole::Admin, 'second');

        $this->actingAs($admin)
            ->put("/admin/users/{$second->id}", $this->payload([
                'username' => 'second',
                'email' => 'second@vermex.test',
                'password' => '',
                'role' => UserRole::BillingOfficer->value,
            ]))
            ->assertRedirect('/admin/users');

        $this->assertSame(UserRole::BillingOfficer, $second->fresh()->role);
    }

    public function test_admin_can_toggle_another_users_status(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $target = $this->user(UserRole::FieldTechnician, 'target');

        $this->actingAs($admin)
            ->from('/admin/users')
            ->post("/admin/users/{$target->id}/toggle-status")
            ->assertRedirect('/admin/users');

        $this->assertSame(UserStatus::Disabled, $target->fresh()->status);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->post("/admin/users/{$target->id}/toggle-status");

        $this->assertSame(UserStatus::Active, $target->fresh()->status);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $target = $this->user(UserRole::FieldTechnician, 'target');

        $this->actingAs($admin)
            ->from('/admin/users')
            ->post("/admin/users/{$target->id}/reset-password", [
                'new_password' => 'BrandNew123!',
            ])
            ->assertRedirect('/admin/users');

        $this->assertTrue(Hash::check('BrandNew123!', $target->fresh()->password));
    }

    public function test_reset_password_requires_minimum_length(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $target = $this->user(UserRole::FieldTechnician, 'target');
        $originalHash = $target->password;

        $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/reset-password", [
                'new_password' => 'short',
            ])
            ->assertSessionHasErrors('new_password');

        $this->assertSame($originalHash, $target->fresh()->password);
    }

    public function test_non_admin_roles_cannot_manage_users(): void
    {
        $billing = $this->user(UserRole::BillingOfficer, 'billing');
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $custodian = $this->user(UserRole::ChemicalCustodian, 'custodian');

        foreach ([$billing, $technician, $custodian] as $account) {
            $this->actingAs($account)->get('/admin/users')->assertForbidden();
            $this->actingAs($account)->get('/admin/users/create')->assertForbidden();
            $this->actingAs($account)->post('/admin/users', $this->payload())->assertForbidden();
        }

        $this->assertSame(3, User::count());
    }

    public function test_billing_accounts_navigation_target_serves_billing_accounts(): void
    {
        $billing = $this->user(UserRole::BillingOfficer, 'billing');

        $this->actingAs($billing)
            ->get('/billing/accounts')
            ->assertOk()
            ->assertViewIs('admin.clients.index');
    }
}
