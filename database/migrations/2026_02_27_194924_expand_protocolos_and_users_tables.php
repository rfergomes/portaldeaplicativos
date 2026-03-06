<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Adicionar ar_online_token no users
        Schema::table('users', function (Blueprint $table) {
            $table->text('ar_online_token')->nullable()->after('password'); // encrypted
        });

        // Expandir tabela protocolos
        Schema::table('protocolos', function (Blueprint $table) {
            $table->foreignId('tipo_protocolo_id')->nullable()->after('id')->constrained('tipo_protocolos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('tipo_protocolo_id')->constrained('users')->nullOnDelete();
            $table->string('referencia_documento')->nullable()->after('user_id'); // ex.: "Ofício 001/2026"
        });

        // Expandir protocolo_destinatarios
        Schema::table('protocolo_destinatarios', function (Blueprint $table) {
            $table->string('cpf_cnpj')->nullable()->after('email');
            $table->json('endereco')->nullable()->after('telefone');
        });

        // Expandir protocolo_envios
        Schema::table('protocolo_envios', function (Blueprint $table) {
            $table->foreignId('destinatario_id')->nullable()->after('protocolo_id')
                ->constrained('protocolo_destinatarios')->nullOnDelete();
            $table->string('canal')->default('email')->after('destinatario_id');
            $table->string('token_usado')->nullable()->after('canal');
        });
    }

    public function down(): void
    {
        Schema::table('protocolo_envios', function (Blueprint $table) {
            $table->dropForeign(['destinatario_id']);
            $table->dropColumn(['destinatario_id', 'canal', 'token_usado']);
        });

        Schema::table('protocolo_destinatarios', function (Blueprint $table) {
            $table->dropColumn(['cpf_cnpj', 'endereco']);
        });

        Schema::table('protocolos', function (Blueprint $table) {
            $table->dropForeign(['tipo_protocolo_id', 'user_id']);
            $table->dropColumn(['tipo_protocolo_id', 'user_id', 'referencia_documento']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ar_online_token');
        });
    }
};
