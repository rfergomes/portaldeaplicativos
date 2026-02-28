<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ar_online_token')) {
                $table->dropColumn('ar_online_token');
            }
            $table->foreignId('token_depto_id')->nullable()->after('password')->constrained('token_deptos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['token_depto_id']);
            $table->dropColumn('token_depto_id');
            $table->text('ar_online_token')->nullable()->after('password');
        });
    }
};
