<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->string('payment_type');
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('payment_date')->useCurrent();

            $table->index(['contract_id']);
            $table->index(['user_id']);
            $table->index(['payment_date']);
            $table->index(['payment_type']);
            $table->index(['payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
