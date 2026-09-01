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
        Schema::create('convencao_termos_aditivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convencao_coletiva_id')
                  ->constrained('convencoes_coletivas')
                  ->onDelete('cascade');
            $table->string('numero_termo', 50); // ex: 01/2027, Aditivo Salarial 2027
            $table->string('titulo'); // ex: Termo Aditivo Salarial 2027/2028
            $table->string('tipo', 50)->default('SALARIAL_ECONOMICO'); // SALARIAL_ECONOMICO, GERAL_RETIFICATIVO, OUTRO
            $table->date('data_assinatura')->nullable();
            $table->date('vigencia_inicio');
            $table->date('vigencia_fim');
            $table->string('arquivo_pdf')->nullable();
            $table->string('arquivo_nome_original')->nullable();
            $table->unsignedBigInteger('arquivo_tamanho')->nullable();
            $table->longText('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['convencao_coletiva_id', 'vigencia_inicio'], 'idx_aditivos_conv_vigencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convencao_termos_aditivos');
    }
};
