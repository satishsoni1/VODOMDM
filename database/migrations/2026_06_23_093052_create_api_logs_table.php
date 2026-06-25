<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);               // mdm_sync | employee_api | whatsapp
            $table->string('action', 100);             // authenticate | fetch_devices | upsert | sync_complete
            $table->string('status', 20)->default('running'); // running | success | failed
            $table->text('summary')->nullable();        // human-readable step description
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('records_in')->nullable();   // rows received from external
            $table->unsignedInteger('records_out')->nullable();  // rows written to DB
            $table->unsignedBigInteger('parent_log_id')->nullable()->index(); // group steps under one run
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
