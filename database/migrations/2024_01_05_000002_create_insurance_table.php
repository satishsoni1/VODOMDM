<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_number')->unique();
            $table->foreignId('insurance_provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coverage_type');
            $table->text('coverage_details')->nullable();
            $table->decimal('premium_amount', 12, 2);
            $table->decimal('sum_insured', 15, 2)->nullable();
            $table->date('start_date');
            $table->date('expiry_date');
            $table->enum('status', ['active', 'expiring', 'expired', 'cancelled'])->default('active');
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index('expiry_date');
        });

        Schema::create('device_insurance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_policy_id')->constrained()->cascadeOnDelete();
            $table->decimal('insured_value', 12, 2)->nullable();
            $table->date('effective_date');
            $table->date('expiry_date');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamps();
        });

        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->date('incident_date');
            $table->string('incident_type');
            $table->text('incident_description');
            $table->decimal('claimed_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('settled_amount', 12, 2)->nullable();
            $table->date('claim_date');
            $table->date('settlement_date')->nullable();
            $table->string('supporting_documents')->nullable();
            $table->enum('status', [
                'draft', 'submitted', 'under_review', 'approved',
                'partially_approved', 'rejected', 'settled', 'closed'
            ])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('claim_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('device_insurance');
        Schema::dropIfExists('insurance_policies');
    }
};
