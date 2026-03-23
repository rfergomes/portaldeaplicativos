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
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->text('acessorios')->nullable()->after('observacao');
        });

        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->text('acessorios')->nullable()->after('observacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->dropColumn('acessorios');
        });

        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->dropColumn('acessorios');
        });
    }
};
