<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->date('received_date');
            $table->integer('quantity_ordered');
            $table->integer('quantity_received');
            $table->integer('quantity_accepted')->default(0);
            $table->integer('quantity_rejected')->default(0);
            $table->string('delivery_challan_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending_qc', 'qc_done', 'partially_accepted', 'accepted', 'rejected'])->default('pending_qc');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
};
