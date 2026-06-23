<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number')->unique();
            $table->foreignId('demand_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('device_specification');
            $table->integer('quantity');
            $table->date('response_deadline')->nullable();
            $table->text('terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'closed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rfq_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'responded', 'no_response'])->default('pending');
            $table->timestamps();
        });

        Schema::create('vendor_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('quotation_number')->nullable();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 15, 2);
            $table->integer('delivery_days')->nullable();
            $table->string('warranty_months')->nullable();
            $table->text('terms')->nullable();
            $table->text('negotiation_notes')->nullable();
            $table->decimal('negotiated_price', 12, 2)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotations');
        Schema::dropIfExists('rfq_vendors');
        Schema::dropIfExists('rfqs');
    }
};
