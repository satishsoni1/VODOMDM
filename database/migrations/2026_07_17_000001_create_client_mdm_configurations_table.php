<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_mdm_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('configuration');
            $table->timestamps();

            $table->unique(['client_id', 'configuration']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_mdm_configurations');
    }
};
