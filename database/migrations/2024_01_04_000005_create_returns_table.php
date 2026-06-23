<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recovery_case_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->date('return_date');
            $table->enum('device_condition', ['new', 'good', 'fair', 'poor', 'damaged', 'not_working'])->default('good');
            $table->boolean('accessories_returned')->default(false);
            $table->text('accessories_list')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->boolean('data_wiped')->default(false);
            $table->timestamp('data_wiped_at')->nullable();
            $table->enum('status', ['inspection_pending', 'approved', 'refurbishment_required', 'rejected'])->default('inspection_pending');
            $table->enum('next_action', ['refurbish', 'reassign', 'dispose', 'repair'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_returns');
    }
};
