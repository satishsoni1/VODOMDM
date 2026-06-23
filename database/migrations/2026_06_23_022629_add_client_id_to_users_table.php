<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('role_id')
                ->constrained('clients')->nullOnDelete();
        });

        if (!DB::table('roles')->where('slug', 'client')->exists()) {
            DB::table('roles')->insert([
                'name'        => 'Client',
                'slug'        => 'client',
                'description' => 'Read-only client portal access — own devices only',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
        DB::table('roles')->where('slug', 'client')->delete();
    }
};
