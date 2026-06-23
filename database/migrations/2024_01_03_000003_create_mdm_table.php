<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('profile_name');
            $table->string('knox_profile_id')->nullable();
            $table->string('mdm_config_id')->nullable();
            $table->text('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('device_mdm_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mdm_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('knox_enrollment_id')->nullable();
            $table->enum('status', ['pending', 'config_pending', 'configured', 'enrolled', 'enrollment_failed', 'unenrolled'])->default('pending');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('unenrolled_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('mdm_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->timestamp('first_sync_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('os_version')->nullable();
            $table->string('battery_level')->nullable();
            $table->string('sim_operator')->nullable();
            $table->string('sim_number')->nullable();
            $table->string('ip_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name')->nullable();
            $table->boolean('is_rooted')->default(false);
            $table->boolean('sim_changed')->default(false);
            $table->json('installed_apps')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();

            $table->index(['device_id', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_sync_logs');
        Schema::dropIfExists('device_mdm_enrollments');
        Schema::dropIfExists('mdm_profiles');
    }
};
