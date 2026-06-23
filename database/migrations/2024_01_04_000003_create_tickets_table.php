<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('sla_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'pending_user', 'resolved', 'closed', 'cancelled'])->default('open');
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->integer('resolution_hours')->nullable();
            $table->timestamps();

            $table->index('ticket_number');
            $table->index(['device_id', 'status']);
        });

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_categories');
    }
};
