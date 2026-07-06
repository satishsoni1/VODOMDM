<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('current_group', 150)->nullable()->after('current_employee_id');
        });

        Schema::table('device_handovers', function (Blueprint $table) {
            $table->string('assignment_group', 150)->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('current_group');
        });

        Schema::table('device_handovers', function (Blueprint $table) {
            $table->dropColumn('assignment_group');
        });
    }
};
