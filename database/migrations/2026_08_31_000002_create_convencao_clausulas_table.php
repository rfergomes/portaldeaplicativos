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
        Schema::create('convencao_clausulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convencao_coletiva_id')
                  ->constrained('convencoes_coletivas')
                  ->onDelete('cascade');
            $table->string('numero', 50); // ex: 76, 03, 04
            $table->string('titulo'); // ex: Contribuições Associativas Mensais
            $table->string('categoria_clausula', 50)->default('CONTRIBUICAO'); // CONTRIBUICAO, SALARIO_NORMATIVO, REAJUSTE, BENEFICIO, GERAL
            $table->longText('texto');
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fim')->nullable();
            $table->boolean('dispara_lembrete_lista_nominal')->default(false);
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['convencao_coletiva_id', 'ordem'], 'idx_clausulas_conv_ordem');
            $table->index(['convencao_coletiva_id', 'dispara_lembrete_lista_nominal'], 'idx_clausulas_lembrete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convencao_clausulas');
    }
};
