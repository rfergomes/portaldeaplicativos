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
        Schema::create('ativo_equipamentos', function (Blueprint $table) {
            $table->id();
            $table->string('identificador')->unique();
            $table->string('descricao');
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            $table->foreignId('fabricante_id')->nullable()->constrained('ativo_fabricantes')->onDelete('set null');
            $table->foreignId('fornecedor_id')->nullable()->constrained('ativo_fornecedores')->onDelete('set null');
            $table->date('data_compra')->nullable();
            $table->decimal('valor_item', 15, 2)->nullable();
            $table->string('valor_nota')->nullable();
            $table->integer('garantia_meses')->nullable();
            $table->enum('status', ['disponivel', 'em_uso', 'manutencao', 'baixado'])->default('disponivel');
            $table->string('tipo_uso')->nullable();
            $table->string('localizacao_atual')->default('Estoque');
            $table->date('data_devolucao_prevista')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('ativo_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipamento_id')->constrained('ativo_equipamentos')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('ativo_usuarios')->onDelete('set null');
            $table->enum('tipo', ['cessao', 'emprestimo', 'devolucao', 'manutencao', 'transferencia']);
            $table->dateTime('data_movimentacao');
            $table->date('data_previsao_devolucao')->nullable();
            $table->dateTime('data_devolucao_real')->nullable();
            $table->foreignId('responsavel_id')->constrained('users');
            $table->string('origem')->nullable();
            $table->string('destino')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('ativo_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipamento_id')->constrained('ativo_equipamentos')->onDelete('cascade');
            $table->foreignId('movimentacao_id')->nullable()->constrained('ativo_movimentacoes')->onDelete('set null');
            $table->string('caminho');
            $table->string('nome_original');
            $table->string('mime_type');
            $table->bigInteger('tamanho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ativo_anexos');
        Schema::dropIfExists('ativo_movimentacoes');
        Schema::dropIfExists('ativo_equipamentos');
    }
};
