<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\InventoryCategory;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category')->default(InventoryCategory::Chemical->value);
            $table->decimal('quantity_in_stock', 10, 2)->default(0.00);
            $table->string('unit')->default('mL');
            $table->decimal('min_threshold', 10, 2)->default(10.00);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->string('batch_number')->nullable();
            $table->string('storage_location')->nullable();
            $table->timestamps();

            $table->index(['category']);
            $table->index(['item_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
