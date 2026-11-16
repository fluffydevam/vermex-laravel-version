<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ClientType;
use App\Enums\ClientStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('street_address');
            $table->string('barangay');
            $table->string('city')->default('Davao City');
            $table->string('client_type')->default(ClientType::Residential->value);
            $table->string('status')->default(ClientStatus::Active->value);
            $table->timestamps();

            $table->index(['status']);
            $table->index(['client_type']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
