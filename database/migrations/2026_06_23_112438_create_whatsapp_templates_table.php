<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('dovesoft_id')->nullable()->index();
            $table->enum('category', ['MARKETING', 'UTILITY', 'AUTHENTICATION'])->default('MARKETING');
            $table->string('language', 10)->default('en');
            $table->enum('status', ['pending', 'approved', 'rejected', 'draft'])->default('draft');
            $table->string('header_type')->nullable();           // TEXT | IMAGE | VIDEO | DOCUMENT
            $table->text('header_text')->nullable();
            $table->text('body_text');
            $table->text('footer_text')->nullable();
            $table->json('buttons')->nullable();                 // CTA / quick-reply buttons
            $table->json('variables')->nullable();               // variable names: ["name","date"]
            $table->json('raw_payload')->nullable();
            $table->text('reject_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
