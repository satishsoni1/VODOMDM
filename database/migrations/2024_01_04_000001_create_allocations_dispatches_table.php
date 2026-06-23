<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_project_id')->nullable()->constrained('client_projects')->nullOnDelete();
            $table->foreignId('allocated_by')->constrained('users')->cascadeOnDelete();
            $table->string('region')->nullable();
            $table->date('allocation_date');
            $table->enum('status', ['reserved', 'allocated', 'cancelled'])->default('reserved');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('dispatch_batches', function (Blueprint $table) {
            $table->id();
            $table->string('dispatch_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_project_id')->nullable()->constrained('client_projects')->nullOnDelete();
            $table->foreignId('courier_partner_id')->nullable()->constrained('courier_partners')->nullOnDelete();
            $table->foreignId('from_location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('dispatched_by')->constrained('users')->cascadeOnDelete();
            $table->string('awb_number')->nullable();
            $table->string('tracking_number')->nullable();
            $table->date('dispatch_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('destination_address')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('destination_state')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->decimal('freight_cost', 10, 2)->nullable();
            $table->enum('status', ['ready', 'in_transit', 'delivered', 'returned', 'lost'])->default('ready');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('awb_number');
        });

        Schema::create('dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['dispatched', 'delivered', 'returned'])->default('dispatched');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_items');
        Schema::dropIfExists('dispatch_batches');
        Schema::dropIfExists('device_allocations');
    }
};
