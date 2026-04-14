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
        Schema::create('socio_caixas', function (Blueprint $table) {
            $table->id();
            $table->string('matricula');
            $table->integer('ano');
            $table->integer('mes');
            $table->string('nome');
            $table->string('tipo_socio')->nullable();
            $table->string('cpf')->nullable();
            $table->decimal('valor', 10, 2)->default(0);
            $table->boolean('pago')->default(false);
            $table->timestamp('data_pagamento')->nullable();
            $table->timestamps();

            $table->unique(['matricula', 'ano', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socio_caixas');
    }
};
