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
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('template_name');
            $table->foreign('template_id')->references('id')->on('whatsapp_templates')->nullOnDelete();
            $table->unsignedBigInteger('campaign_id')->nullable()->after('template_id');
            $table->foreign('campaign_id')->references('id')->on('whatsapp_campaigns')->nullOnDelete();
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound')->after('status');
            $table->text('reply_to_message_id')->nullable()->after('direction');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['campaign_id']);
            $table->dropColumn(['template_id', 'campaign_id', 'direction', 'reply_to_message_id']);
        });
    }
};
