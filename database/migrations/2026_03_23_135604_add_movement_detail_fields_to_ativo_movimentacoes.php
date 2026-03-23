<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->string('local_manutencao')->nullable()->after('dados_cedente');
            $table->string('contato_manutencao')->nullable()->after('local_manutencao');
            $table->unsignedBigInteger('destino_departamento_id')->nullable()->after('contato_manutencao');
            $table->unsignedBigInteger('destino_estacao_id')->nullable()->after('destino_departamento_id');

            $table->foreign('destino_departamento_id')->references('id')->on('ativo_departamentos')->nullOnDelete();
            $table->foreign('destino_estacao_id')->references('id')->on('ativo_estacoes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['destino_departamento_id']);
            $table->dropForeign(['destino_estacao_id']);
            $table->dropColumn(['local_manutencao', 'contato_manutencao', 'destino_departamento_id', 'destino_estacao_id']);
        });
    }
};
