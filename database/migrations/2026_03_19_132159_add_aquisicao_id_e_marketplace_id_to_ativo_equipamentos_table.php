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
            $table->foreignId('aquisicao_id')->nullable()->after('fornecedor_id')->constrained('ativo_aquisicoes')->nullOnDelete();
            $table->foreignId('marketplace_id')->nullable()->after('aquisicao_id')->constrained('ativo_marketplaces')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->dropForeign(['aquisicao_id']);
            $table->dropForeign(['marketplace_id']);
            $table->dropColumn(['aquisicao_id', 'marketplace_id']);
        });
    }
};
