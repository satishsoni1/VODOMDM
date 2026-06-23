<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('device_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('device_brands')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('device_categories')->cascadeOnDelete();
            $table->string('model_name');
            $table->string('model_number')->nullable();
            $table->string('os')->nullable();
            $table->string('os_version')->nullable();
            $table->string('ram')->nullable();
            $table->string('storage')->nullable();
            $table->string('screen_size')->nullable();
            $table->string('battery')->nullable();
            $table->text('specifications')->nullable();
            $table->decimal('standard_cost', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('courier_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('tracking_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->enum('type', ['authorized', 'third_party'])->default('authorized');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('insurance_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_providers');
        Schema::dropIfExists('service_centers');
        Schema::dropIfExists('courier_partners');
        Schema::dropIfExists('device_models');
        Schema::dropIfExists('device_categories');
        Schema::dropIfExists('device_brands');
    }
};
