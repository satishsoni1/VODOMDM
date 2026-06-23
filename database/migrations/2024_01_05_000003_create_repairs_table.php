<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('rma_number')->unique();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('insurance_claim_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('fault_description');
            $table->text('detailed_notes')->nullable();
            $table->date('sent_date');
            $table->date('estimated_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->boolean('under_warranty')->default(false);
            $table->enum('repair_type', ['warranty', 'paid', 'insurance'])->default('paid');
            $table->text('repair_notes')->nullable();
            $table->enum('status', [
                'sent', 'received_at_sc', 'under_repair', 'awaiting_parts',
                'repaired', 'replaced', 'unrepairable', 'returned'
            ])->default('sent');
            $table->enum('outcome', ['repaired', 'replaced', 'unrepairable'])->nullable();
            $table->foreignId('replacement_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
