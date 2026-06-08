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
        Schema::create('demandas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao');
            $table->dateTime('prazo')->nullable();
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            
            // Relação com o criador (Usuário do Sistema)
            $table->foreignId('criador_id')->constrained('users')->onDelete('cascade');
            
            // Definição do Responsável
            $table->enum('tipo_responsavel', ['usuario', 'externo'])->default('usuario');
            $table->foreignId('responsavel_usuario_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Controle de Leitura/Visualização da Demanda pelo Responsável Interno
            $table->boolean('lida_pelo_responsavel')->default(false);
            
            // Dados do contato externo
            $table->string('responsavel_nome')->nullable();
            $table->string('responsavel_telefone')->nullable(); // Celular para WhatsApp
            $table->string('responsavel_email')->nullable();
            
            // Ciclo de Vida da Demanda
            $table->enum('status', ['aberta', 'aguardando', 'executada', 'nao_executada', 'cancelada'])->default('aberta');
            
            // Devolutiva do Responsável (Registrada pelo criador ou pelo usuário atribuído)
            $table->text('motivo_devolutiva')->nullable();
            $table->text('observacoes')->nullable();
            $table->dateTime('devolutiva_em')->nullable();
            
            $table->timestamps();
        });

        Schema::create('demanda_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demanda_id')->constrained('demandas')->onDelete('cascade');
            $table->string('item');
            $table->boolean('concluido')->default(false);
            $table->timestamps();
        });

        Schema::create('demanda_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demanda_id')->constrained('demandas')->onDelete('cascade');
            $table->string('caminho');
            $table->string('nome_original');
            $table->enum('tipo_origem', ['criador', 'devolutiva']);
            $table->timestamps();
        });

        Schema::create('demanda_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demanda_id')->constrained('demandas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('acao'); // 'criada', 'notificada_whatsapp', 'encaminhada', 'alterada', 'devolutiva', 'cancelada'
            $table->text('descricao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demanda_historicos');
        Schema::dropIfExists('demanda_anexos');
        Schema::dropIfExists('demanda_checklists');
        Schema::dropIfExists('demandas');
    }
};
