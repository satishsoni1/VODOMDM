<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_device_params', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mdm_device_id')->unique();
            $table->foreign('mdm_device_id')->references('id')->on('mdm_devices')->cascadeOnDelete();
            $table->dateTime('recorded_at')->nullable();
            $table->json('raw_json')->nullable();
            $table->timestamp('pg_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_device_params');
    }
};
