<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('demand_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->string('payment_terms')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('warranty_months')->nullable();
            $table->text('special_instructions')->nullable();
            $table->enum('status', ['draft', 'approved', 'sent', 'acknowledged', 'partial', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('po_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->decimal('invoice_amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_invoices');
        Schema::dropIfExists('purchase_orders');
    }
};
