<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('trigger_reason', ['resignation', 'termination', 'transfer', 'long_leave', 'other'])->default('resignation');
            $table->date('exit_date')->nullable();
            $table->date('recovery_due_date')->nullable();
            $table->date('pickup_scheduled_date')->nullable();
            $table->date('recovered_date')->nullable();
            $table->string('pickup_address')->nullable();
            $table->enum('status', ['open', 'contacted', 'pickup_scheduled', 'recovered', 'escalated', 'closed', 'written_off'])->default('open');
            $table->integer('follow_up_count')->default(0);
            $table->timestamp('last_follow_up_at')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'status']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recovery_case_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('called_by')->constrained('users')->cascadeOnDelete();
            $table->string('phone_number');
            $table->datetime('call_datetime');
            $table->integer('duration_seconds')->nullable();
            $table->enum('outcome', [
                'connected', 'no_answer', 'switched_off', 'invalid_number',
                'refused', 'agreed_to_return', 'promised', 'call_back_later'
            ])->default('connected');
            $table->date('promise_date')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('recovery_cases');
    }
};
