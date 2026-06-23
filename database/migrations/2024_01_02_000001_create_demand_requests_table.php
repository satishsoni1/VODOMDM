<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_project_id')->nullable()->constrained('client_projects')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('device_model_id')->nullable()->constrained('device_models')->nullOnDelete();
            $table->string('device_specification')->nullable();
            $table->integer('quantity');
            $table->date('required_date')->nullable();
            $table->decimal('budget_amount', 15, 2)->nullable();
            $table->string('division')->nullable();
            $table->string('region')->nullable();
            $table->text('justification')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'converted_to_po'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_requests');
    }
};
