<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('channel', ['call', 'whatsapp', 'sms', 'email', 'meeting', 'other'])->default('call');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->string('stakeholder_type')->nullable();
            $table->unsignedBigInteger('stakeholder_id')->nullable();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recovery_case_id')->nullable()->constrained()->nullOnDelete();
            $table->datetime('communication_datetime');
            $table->string('subject')->nullable();
            $table->text('outcome')->nullable();
            $table->text('remarks')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['stakeholder_type', 'stakeholder_id']);
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->string('subject');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'missed', 'rescheduled'])->default('pending');
            $table->date('completed_date')->nullable();
            $table->text('outcome')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['scheduled_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('communication_logs');
    }
};
