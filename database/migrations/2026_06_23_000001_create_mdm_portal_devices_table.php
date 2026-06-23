<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_portal_devices', function (Blueprint $table) {
            $table->id();

            // ── MDM Portal identity ──────────────────────────────────────────
            $table->string('mdm_number', 100)->unique();   // "number" in Headwind MDM
            $table->string('imei', 50)->nullable()->index();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('phone', 20)->nullable();
            $table->text('description')->nullable();

            // ── MDM configuration ────────────────────────────────────────────
            $table->string('mdm_group', 100)->nullable();
            $table->string('configuration', 100)->nullable();
            $table->string('launcher_version', 30)->nullable();
            $table->boolean('mdm_mode')->default(false);
            $table->boolean('kiosk_mode')->default(false);
            $table->string('default_launcher', 150)->nullable();

            // ── Device status ────────────────────────────────────────────────
            $table->string('device_status', 20)->default('unknown'); // on / off / unknown
            $table->string('permission_status', 255)->nullable();
            $table->text('installation_status')->nullable();         // raw app list text
            $table->dateTime('sync_time')->nullable();              // last heartbeat
            $table->bigInteger('last_update')->nullable();          // unix timestamp from MDM
            $table->string('mdm_status', 50)->nullable();          // Active / Inactive

            // ── Hardware info ────────────────────────────────────────────────
            $table->string('model', 100)->nullable();
            $table->string('android_version', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('public_ip', 100)->nullable();

            // ── Location ─────────────────────────────────────────────────────
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->string('location_raw', 255)->nullable();
            $table->string('division', 100)->nullable();

            // ── JSON payload ──────────────────────────────────────────────────
            $table->json('info_json')->nullable();

            // ── Enrollment ───────────────────────────────────────────────────
            $table->dateTime('enrollment_date')->nullable();

            // ── Links to our system ───────────────────────────────────────────
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();

            // ── Sync tracking ─────────────────────────────────────────────────
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_portal_devices');
    }
};
