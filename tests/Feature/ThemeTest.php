<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_ships_theme_toggle_and_prepaint_script(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('theme-toggle', false)
            ->assertSee('vermex-theme', false)
            ->assertSee('$store.theme.toggle()', false)
            ->assertSee('Switch to dark mode')
            ->assertSee('Switch to light mode')
            ->assertSee('js/vendor/alpine.min.js', false);
    }

    public function test_authenticated_shell_ships_theme_toggle(): void
    {
        $this->seed();
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('theme-toggle', false)
            ->assertSee('vermex-theme', false)
            ->assertSee('$store.theme.toggle()', false);
    }
}
