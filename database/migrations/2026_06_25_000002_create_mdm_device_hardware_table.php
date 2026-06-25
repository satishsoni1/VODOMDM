<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_device_hardware', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mdm_device_id')->unique();
            $table->foreign('mdm_device_id')->references('id')->on('mdm_devices')->cascadeOnDelete();
            $table->dateTime('recorded_at')->nullable();
            $table->unsignedTinyInteger('battery')->nullable(); // 0–100 %
            $table->unsignedBigInteger('total_ram')->nullable();
            $table->unsignedBigInteger('available_ram')->nullable();
            $table->unsignedBigInteger('total_internal_storage')->nullable();
            $table->unsignedBigInteger('available_internal_storage')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->string('android_id', 100)->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamp('pg_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_device_hardware');
    }
};
