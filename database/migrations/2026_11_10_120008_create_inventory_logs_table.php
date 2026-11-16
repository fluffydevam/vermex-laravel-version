<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('job_order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('site_inspection_id')->nullable()->constrained('site_inspections')->onDelete('set null');
            $table->string('action_type');
            $table->decimal('quantity_changed', 10, 2); // signed: positive = added, negative = deducted
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id']);
            $table->index(['performed_by']);
            $table->index(['job_order_id']);
            $table->index(['site_inspection_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
