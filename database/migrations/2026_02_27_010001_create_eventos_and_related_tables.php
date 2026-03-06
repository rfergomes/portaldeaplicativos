<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable();
            $table->string('local')->nullable();
            $table->decimal('valor_inteira', 10, 2)->default(0);
            $table->decimal('valor_meia', 10, 2)->default(0);
            $table->boolean('encerrado')->default(false);
            $table->timestamps();
        });

        Schema::create('lotes_convite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->string('nome');
            $table->unsignedInteger('quantidade_total');
            $table->unsignedInteger('quantidade_disponivel');
            $table->timestamps();
        });

        Schema::create('convites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('lotes_convite');
            $table->enum('tipo', ['inteira', 'meia'])->default('inteira');
            $table->decimal('valor', 10, 2);
            $table->string('codigo')->unique();
            $table->boolean('utilizado')->default(false);
            $table->timestamps();
        });

        Schema::create('convidados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convite_id')->constrained('convites')->cascadeOnDelete();
            $table->string('nome');
            $table->string('documento', 20)->nullable();
            $table->boolean('presente')->default(false);
            $table->timestamps();
        });

        Schema::create('vendas_convite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convite_id')->constrained('convites')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->decimal('valor_venda', 10, 2);
            $table->enum('status_pagamento', ['pago', 'pendente', 'cancelado'])->default('pago');
            $table->timestamp('data_venda')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas_convite');
        Schema::dropIfExists('convidados');
        Schema::dropIfExists('convites');
        Schema::dropIfExists('lotes_convite');
        Schema::dropIfExists('eventos');
    }
};

