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
        Schema::table('ativo_licenca_equipamento', function (Blueprint $table) {
            if (!Schema::hasColumn('ativo_licenca_equipamento', 'quantidade')) {
                $table->integer('quantidade')->default(1)->after('equipamento_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_licenca_equipamento', function (Blueprint $table) {
            $table->dropColumn('quantidade');
        });
    }
};
