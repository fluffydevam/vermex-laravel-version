<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_login_screen_has_password_visibility_toggle(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('showPassword', false)
            ->assertSee('x-bind:type', false)
            ->assertSee('x-text', false)
            ->assertSee('Show')
            ->assertSee('Hide');
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'Admin123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isAdmin());
    }

    public function test_users_can_authenticate_using_email(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'admin@vermexpest.com',
            'password' => 'Admin123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    public function test_authentication_fails_with_invalid_credentials(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->seed();
        $user = User::where('username', 'admin')->first();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_each_role_lands_on_its_own_dashboard(): void
    {
        $cases = [
            [UserRole::BillingOfficer, 'billinguser', 'billing.dashboard'],
            [UserRole::FieldTechnician, 'fielduser', 'field.dashboard'],
            [UserRole::ChemicalCustodian, 'custodianuser', 'custodian.dashboard'],
        ];

        foreach ($cases as [$role, $username, $route]) {
            User::create([
                'username' => $username,
                'email' => "{$username}@vermex.test",
                'password' => 'password',
                'first_name' => ucfirst($username),
                'last_name' => 'Tester',
                'role' => $role->value,
                'status' => UserStatus::Active->value,
            ]);

            $this->post('/login', [
                'username' => $username,
                'password' => 'password',
            ])->assertRedirect(route($route));

            $this->assertAuthenticated();
            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_non_admin_intended_admin_url_falls_back_to_own_dashboard(): void
    {
        $technician = User::create([
            'username' => 'intendedtech',
            'email' => 'intendedtech@vermex.test',
            'password' => 'password',
            'first_name' => 'Intended',
            'last_name' => 'Tech',
            'role' => UserRole::FieldTechnician->value,
            'status' => UserStatus::Active->value,
        ]);

        // Guest tries an admin URL first, then logs in.
        $this->get('/admin/dashboard')->assertRedirect('/login');

        $this->post('/login', [
            'username' => $technician->username,
            'password' => 'password',
        ])->assertRedirect(route('field.dashboard'));
    }

    public function test_intended_url_is_honoured_when_role_allows_it(): void
    {
        User::create([
            'username' => 'deepfieldtech',
            'email' => 'deepfieldtech@vermex.test',
            'password' => 'password',
            'first_name' => 'Deep',
            'last_name' => 'Tech',
            'role' => UserRole::FieldTechnician->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->get('/field/job-orders')->assertRedirect('/login');

        $this->post('/login', [
            'username' => 'deepfieldtech',
            'password' => 'password',
        ])->assertRedirect('/field/job-orders');
    }

    public function test_logout_clears_session(): void
    {
        $this->seed();
        $user = User::where('username', 'admin')->first();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
}
