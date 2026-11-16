<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ContractStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('contract_name');
            $table->string('contract_status')->default(ContractStatus::ToBeContracted->value);
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->decimal('contract_value', 10, 2)->default(0.00);
            $table->timestamps();

            $table->index(['contract_status']);
            $table->index(['contract_start_date', 'contract_end_date']);
            $table->index(['client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
