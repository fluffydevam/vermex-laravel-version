<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $this->seed();

        $admin = User::where('role', UserRole::Admin->value)->firstOrFail();

        $this->actingAs($admin)->get('/')->assertRedirect('/admin/dashboard');
    }
}
