<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocolos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->string('assunto');
            $table->text('corpo');
            $table->string('canal')->default('email');
            $table->string('status')->default('pendente'); // pendente, enviado, falha, concluido
            $table->timestamp('agendado_para')->nullable();
            $table->timestamps();
        });

        Schema::create('protocolo_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_id')->constrained('protocolos')->cascadeOnDelete();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->json('metadados')->nullable();
            $table->timestamps();
        });

        Schema::create('protocolo_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_id')->constrained('protocolos')->cascadeOnDelete();
            $table->string('nome_original');
            $table->string('caminho_armazenado');
            $table->unsignedBigInteger('tamanho_bytes')->nullable();
            $table->string('hash')->nullable();
            $table->timestamps();
        });

        Schema::create('protocolo_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_id')->constrained('protocolos')->cascadeOnDelete();
            $table->string('id_email_externo')->nullable();
            $table->string('status')->default('queued');
            $table->text('ultima_resposta')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->timestamp('lido_em')->nullable();
            $table->timestamps();
        });

        Schema::create('protocolo_comprovantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_id')->constrained('protocolos')->cascadeOnDelete();
            $table->text('pdf_base64')->nullable();
            $table->string('hash_documento')->nullable();
            $table->timestamps();
        });

        Schema::create('carimbos_tempo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_id')->constrained('protocolos')->cascadeOnDelete();
            $table->string('provedor')->default('ar-online');
            $table->string('token')->nullable();
            $table->json('payload_completo')->nullable();
            $table->timestamp('carimbado_em')->nullable();
            $table->timestamps();
        });
    }

        public function down(): void
    {
        Schema::dropIfExists('carimbos_tempo');
        Schema::dropIfExists('protocolo_comprovantes');
        Schema::dropIfExists('protocolo_envios');
        Schema::dropIfExists('protocolo_anexos');
        Schema::dropIfExists('protocolo_destinatarios');
        Schema::dropIfExists('protocolos');
    }
};

