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
        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->decimal('valor_orcamento', 10, 2)->nullable()->after('observacao');
            $table->string('dados_cedente')->nullable()->after('valor_orcamento');
            $table->date('data_retirada')->nullable()->after('dados_cedente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->dropColumn(['valor_orcamento', 'dados_cedente', 'data_retirada']);
        });
    }
};
