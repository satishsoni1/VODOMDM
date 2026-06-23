<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('action', ['submitted', 'approved', 'rejected', 'escalated', 'recalled'])->default('submitted');
            $table->text('comments')->nullable();
            $table->integer('approval_level')->default(1);
            $table->timestamp('actioned_at')->useCurrent();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('document_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('depreciation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_value', 12, 2);
            $table->decimal('current_value', 12, 2);
            $table->decimal('depreciation_amount', 12, 2);
            $table->decimal('depreciation_rate', 5, 2);
            $table->string('depreciation_method')->default('straight_line');
            $table->date('as_of_date');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['device_id', 'as_of_date']);
        });

        Schema::create('disposal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', ['beyond_repair', 'lost', 'obsolete', 'theft', 'other'])->default('beyond_repair');
            $table->string('disposal_method')->nullable();
            $table->decimal('residual_value', 12, 2)->default(0);
            $table->decimal('write_off_value', 12, 2)->nullable();
            $table->date('disposal_date');
            $table->string('disposal_certificate')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'disposed', 'written_off'])->default('pending_approval');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposal_records');
        Schema::dropIfExists('depreciation_records');
        Schema::dropIfExists('document_attachments');
        Schema::dropIfExists('approval_logs');
    }
};
