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
        Schema::create('ativo_aquisicoes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_nf')->nullable();
            $table->string('chave_acesso')->nullable();
            $table->date('data_aquisicao')->nullable();
            $table->foreignId('fornecedor_id')->nullable()->constrained('ativo_fornecedores')->nullOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained('ativo_marketplaces')->nullOnDelete();
            $table->decimal('valor_frete', 10, 2)->nullable();
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ativo_aquisicoes');
    }
};
