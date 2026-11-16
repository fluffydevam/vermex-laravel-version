<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\JobOrderStatus;
use App\Enums\JobOrderPriority;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('client_name')->nullable(); // snapshot
            $table->string('service_address')->nullable(); // snapshot
            $table->string('service_type');
            $table->date('scheduled_date');
            $table->string('service_window');
            $table->string('priority')->default(JobOrderPriority::Standard->value);
            $table->string('route_status')->default(JobOrderStatus::Scheduled->value);
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->text('comments')->nullable();
            $table->json('area_findings')->nullable();
            $table->timestamps();

            $table->index(['client_id']);
            $table->index(['contract_id']);
            $table->index(['technician_id']);
            $table->index(['route_status']);
            $table->index(['scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
