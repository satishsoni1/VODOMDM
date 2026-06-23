<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_project_id')->nullable()->constrained('client_projects')->nullOnDelete();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('region')->nullable();
            $table->string('hq')->nullable();
            $table->string('abm')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_email')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->date('joining_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'resigned', 'terminated', 'on_leave'])->default('active');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
