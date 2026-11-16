<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\SiteInspection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintPdfTest extends TestCase
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

    private function inspection(User $technician): SiteInspection
    {
        $client = Client::create([
            'client_name' => 'Print Client',
            'first_name' => 'Test',
            'last_name' => 'Client',
            'phone_number' => '09000000000',
            'street_address' => '123 Main St',
            'barangay' => 'Matina',
            'city' => 'Davao City',
            'client_type' => 'Commercial',
            'status' => 'active',
        ]);

        return SiteInspection::create([
            'client_id' => $client->id,
            'technician_id' => $technician->id,
            'client_name' => $client->client_name,
            'service_address' => $client->address,
            'inspection_status' => 'Inspection Complete',
            'inspection_date' => today(),
            'conditions_json' => [],
            'findings_json' => [],
        ]);
    }

    public function test_admin_inspection_show_exposes_print_and_pdf_hooks(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $technician = $this->user(UserRole::FieldTechnician, 'tech');
        $inspection = $this->inspection($technician);

        $response = $this->actingAs($admin)
            ->get("/admin/inspections/{$inspection->id}")
            ->assertOk();

        $response->assertSee('id="printable-report"', false);
        $response->assertSee('window.print()', false);
        $response->assertSee('downloadPdf(', false);
        $response->assertSee('js/vendor/html2pdf.bundle.min.js', false);
    }

    public function test_report_views_expose_print_and_pdf_hooks(): void
    {
        $admin = $this->user(UserRole::Admin, 'admin');
        $billing = $this->user(UserRole::BillingOfficer, 'billing');

        foreach (['/admin/reports', '/billing/reports'] as $path) {
            $account = $path === '/admin/reports' ? $admin : $billing;

            $response = $this->actingAs($account)->get($path)->assertOk();

            $response->assertSee('id="printable-report"', false);
            $response->assertSee('window.print()', false);
            $response->assertSee('downloadPdf(', false);
            $response->assertSee('js/vendor/html2pdf.bundle.min.js', false);
        }
    }

    public function test_html2pdf_bundle_is_vendored_locally(): void
    {
        $path = public_path('js/vendor/html2pdf.bundle.min.js');

        $this->assertFileExists($path);
        $this->assertGreaterThan(500_000, filesize($path));
    }
}
