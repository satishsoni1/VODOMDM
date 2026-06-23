<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdm_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->string('filename');
            $table->integer('total_rows')->default(0);
            $table->integer('imported')->default(0);
            $table->integer('updated')->default(0);
            $table->integer('skipped')->default(0);
            $table->integer('auto_matched')->default(0);
            $table->string('status', 20)->default('completed'); // completed / failed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdm_import_logs');
    }
};
