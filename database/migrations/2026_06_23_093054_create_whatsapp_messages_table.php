<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('to_phone', 20);
            $table->string('to_name')->nullable();
            $table->text('message_text');
            $table->string('template_name', 100)->nullable();
            $table->json('variables')->nullable();         // template variable values
            $table->string('trigger_event', 60)           // manual | device_offline | handover_created |
                ->default('manual');                       //   ticket_opened | recovery_initiated | enrollment
            $table->string('trigger_entity_type', 40)->nullable(); // device | employee | ticket | handover
            $table->unsignedBigInteger('trigger_entity_id')->nullable();
            $table->timestamp('scheduled_at')->nullable(); // null = send immediately
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 20)->default('pending'); // pending | sent | failed | cancelled
            $table->json('api_response')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['trigger_event', 'trigger_entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
