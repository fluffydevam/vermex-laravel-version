<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\InspectionStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('client_name')->nullable(); // snapshot
            $table->string('client_email')->nullable(); // snapshot
            $table->text('service_address')->nullable(); // snapshot
            $table->string('account_type')->nullable();
            $table->string('inspection_status')->default(InspectionStatus::PendingVisit->value);
            $table->date('inspection_date')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->integer('ilt_qty')->default(0);
            $table->string('ilt_remarks')->nullable();
            $table->integer('rat_cage_qty')->default(0);
            $table->string('rat_cage_remarks')->nullable();
            $table->integer('rat_bait_qty')->default(0);
            $table->string('rat_bait_remarks')->nullable();
            $table->integer('glue_trap_qty')->default(0);
            $table->string('glue_trap_remarks')->nullable();
            $table->string('vermex_representative')->nullable();
            $table->string('client_representative')->nullable();
            $table->json('conditions_json')->nullable();
            $table->json('findings_json')->nullable();
            $table->boolean('pests_found')->default(false);
            $table->timestamps();

            $table->index(['contract_id']);
            $table->index(['client_id']);
            $table->index(['technician_id']);
            $table->index(['inspection_status']);
            $table->index(['inspection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_inspections');
    }
};
