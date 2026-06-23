<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->unique();
            $table->string('imei1')->nullable()->unique();
            $table->string('imei2')->nullable()->unique();
            $table->foreignId('device_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grn_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('box_number')->nullable();
            $table->string('color')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('warranty_months')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('accessories')->nullable();
            $table->enum('lifecycle_status', [
                'ordered', 'received', 'qc_pending', 'in_stock',
                'config_pending', 'configured', 'enrollment_failed', 'enrolled',
                'reserved', 'allocated', 'ready_to_dispatch', 'in_transit',
                'delivered', 'assigned', 'activated',
                'under_repair', 'awaiting_parts', 'repaired', 'replaced',
                'recovery_pending', 'recovered',
                'refurbishing', 'refurbished',
                'pending_disposal', 'disposed', 'written_off', 'lost'
            ])->default('received');
            $table->enum('condition', ['new', 'good', 'fair', 'poor', 'damaged'])->default('new');
            $table->foreignId('current_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('current_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['serial_number', 'imei1', 'asset_tag']);
            $table->index('lifecycle_status');
            $table->index('current_employee_id');
        });

        Schema::create('device_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->enum('status', ['with_device', 'returned', 'lost', 'damaged'])->default('with_device');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_accessories');
        Schema::dropIfExists('devices');
    }
};
