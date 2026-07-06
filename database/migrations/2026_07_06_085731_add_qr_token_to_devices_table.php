<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('qr_token', 36)->nullable()->unique()->after('id');
        });

        DB::table('devices')->whereNull('qr_token')->orderBy('id')->chunkById(200, function ($devices) {
            foreach ($devices as $device) {
                DB::table('devices')->where('id', $device->id)->update(['qr_token' => (string) Str::uuid()]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
