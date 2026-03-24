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
        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->string('chave_acesso')->nullable()->after('numero_nf');
            $table->foreignId('marketplace_id')->nullable()->after('fornecedor_id')->constrained('ativo_marketplaces')->nullOnDelete();
            $table->decimal('valor_frete', 12, 2)->nullable()->after('valor_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->dropForeign(['marketplace_id']);
            $table->dropColumn(['chave_acesso', 'marketplace_id', 'valor_frete']);
        });
    }
};
