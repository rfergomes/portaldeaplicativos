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
        Schema::create('convencoes_coletivas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('categoria', 50)->default('QUIMICA'); // QUIMICA, FARMACEUTICA
            $table->date('vigencia_inicio');
            $table->date('vigencia_fim');
            $table->string('data_base', 50); // ex: Novembro, Abril
            $table->text('abrangencia')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['categoria', 'ativo'], 'idx_convencoes_cat_ativo');
            $table->index(['vigencia_inicio', 'vigencia_fim'], 'idx_convencoes_vigencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convencoes_coletivas');
    }
};
