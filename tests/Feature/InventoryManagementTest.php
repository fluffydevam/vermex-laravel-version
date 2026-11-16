<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'admin',
            'email' => 'admin@vermex.test',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'role' => UserRole::Admin->value,
            'status' => 'active',
        ]);
    }

    private function custodian(): User
    {
        return User::create([
            'username' => 'custodian',
            'email' => 'custodian@vermex.test',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'Custodian',
            'role' => UserRole::ChemicalCustodian->value,
            'status' => 'active',
        ]);
    }

    private function item(array $overrides = []): InventoryItem
    {
        return InventoryItem::create(array_merge([
            'item_name' => 'Baygon',
            'category' => 'Chemical',
            'quantity_in_stock' => 50,
            'unit' => 'Liters',
            'min_threshold' => 10,
            'unit_cost' => 25,
        ], $overrides));
    }

    public function test_admin_can_create_item_and_opening_stock_is_logged(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/inventory', [
                'item_name' => 'Rat Bait',
                'category' => 'Chemical',
                'quantity_in_stock' => 12,
                'unit' => 'Kg',
                'min_threshold' => 5,
                'unit_cost' => 100,
                'batch_number' => 'B-01',
                'storage_location' => 'Shelf A',
            ])
            ->assertRedirect('/admin/inventory');

        $item = InventoryItem::where('item_name', 'Rat Bait')->firstOrFail();

        $this->assertSame('12.00', $item->quantity_in_stock);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_item_id' => $item->id,
            'action_type' => 'Initial Stock',
            'performed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_add_stock(): void
    {
        $admin = $this->admin();
        $item = $this->item();

        $this->actingAs($admin)
            ->post("/admin/inventory/{$item->id}/adjust", [
                'adjustment_type' => 'add',
                'adjust_amount' => 10,
                'remarks' => 'Delivery',
            ])
            ->assertRedirect();

        $this->assertSame('60.00', $item->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_item_id' => $item->id,
            'action_type' => 'Stock Added',
            'quantity_changed' => 10,
        ]);
    }

    public function test_admin_can_set_exact_stock_count(): void
    {
        $admin = $this->admin();
        $item = $this->item();

        $this->actingAs($admin)
            ->post("/admin/inventory/{$item->id}/adjust", [
                'adjustment_type' => 'set',
                'adjust_amount' => 7,
            ])
            ->assertRedirect();

        $this->assertSame('7.00', $item->fresh()->quantity_in_stock);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_item_id' => $item->id,
            'action_type' => 'Stock Audit Set',
            'quantity_changed' => -43,
        ]);
    }

    public function test_stock_cannot_be_deducted_below_zero(): void
    {
        $admin = $this->admin();
        $item = $this->item(['quantity_in_stock' => 3]);

        $this->actingAs($admin)
            ->post("/admin/inventory/{$item->id}/adjust", [
                'adjustment_type' => 'subtract',
                'adjust_amount' => 5,
            ])
            ->assertSessionHasErrors('adjust_amount');

        $this->assertSame('3.00', $item->fresh()->quantity_in_stock);
        $this->assertSame(0, InventoryLog::where('inventory_item_id', $item->id)->count());
    }

    public function test_custodian_can_adjust_but_not_create_items(): void
    {
        $custodian = $this->custodian();
        $item = $this->item();

        $this->actingAs($custodian)
            ->post("/custodian/inventory/{$item->id}/adjust", [
                'adjustment_type' => 'subtract',
                'adjust_amount' => 5,
            ])
            ->assertRedirect();

        $this->assertSame('45.00', $item->fresh()->quantity_in_stock);

        $this->actingAs($custodian)->get('/admin/inventory')->assertForbidden();
        $this->actingAs($custodian)->post('/admin/inventory', [])->assertForbidden();
    }

    public function test_field_technician_cannot_adjust_stock(): void
    {
        $technician = User::create([
            'username' => 'tech',
            'email' => 'tech@vermex.test',
            'password' => 'password',
            'first_name' => 'Test',
            'last_name' => 'Tech',
            'role' => UserRole::FieldTechnician->value,
            'status' => 'active',
        ]);

        $item = $this->item();

        $this->actingAs($technician)
            ->post("/custodian/inventory/{$item->id}/adjust", [
                'adjustment_type' => 'add',
                'adjust_amount' => 5,
            ])
            ->assertForbidden();
    }
}
