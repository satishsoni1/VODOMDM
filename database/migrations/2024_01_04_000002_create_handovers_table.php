<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('handover_number')->unique();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_project_id')->nullable()->constrained('client_projects')->nullOnDelete();
            $table->foreignId('handed_over_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dispatch_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('handover_date');
            $table->string('handover_location')->nullable();
            $table->string('handover_city')->nullable();
            $table->boolean('acknowledgement_received')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('acknowledgement_file')->nullable();
            $table->enum('condition_at_handover', ['new', 'good', 'fair', 'poor'])->default('new');
            $table->text('accessories_handed')->nullable();
            $table->enum('status', ['assigned', 'activated', 'returned'])->default('assigned');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('ownership_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('ownership_type', ['employee', 'client', 'warehouse', 'service_center', 'disposed'])->default('employee');
            $table->timestamp('from_date');
            $table->timestamp('to_date')->nullable();
            $table->string('transfer_reason')->nullable();
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['device_id', 'from_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ownership_history');
        Schema::dropIfExists('device_handovers');
    }
};
