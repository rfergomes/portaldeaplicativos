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
        Schema::table('desfiliacoes', function (Blueprint $table) {
            $table->timestamp('aceite_lgpd_em')->nullable()->after('ip_solicitacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desfiliacoes', function (Blueprint $table) {
            $table->dropColumn('aceite_lgpd_em');
        });
    }
};
