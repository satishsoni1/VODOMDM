<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // PG hmdm_device.id (not auto-increment)
            $table->string('pg_number', 100)->unique(); // MDM device number
            $table->string('imei', 50)->nullable()->index();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('phone', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('mdm_group', 100)->nullable();
            $table->string('configuration', 100)->nullable();
            $table->string('launcher_version', 30)->nullable();
            $table->boolean('mdm_mode')->default(false);
            $table->boolean('kiosk_mode')->default(false);
            $table->string('default_launcher', 150)->nullable();
            $table->string('device_status', 20)->default('unknown');
            $table->string('permission_status', 255)->nullable();
            $table->text('installation_status')->nullable();
            $table->dateTime('sync_time')->nullable();
            $table->string('mdm_status', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('android_version', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('public_ip', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->string('division', 100)->nullable();
            $table->dateTime('enrollment_date')->nullable();
            $table->foreignId('local_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('local_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('pg_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_devices');
    }
};
