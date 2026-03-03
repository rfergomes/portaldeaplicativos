<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_periodo_id')->constrained('agenda_periodos')->onDelete('cascade');
            $table->foreignId('colonia_id')->constrained('colonias')->onDelete('cascade');

            // Qual acomodação ele pegou (nulo se for fila de espera ou cota flexível sem quarto ainda)
            $table->foreignId('colonia_acomodacao_id')->nullable()->constrained('colonia_acomodacaos')->onDelete('set null');

            // Hospede amarrado (nulo caso a reserva seja apenas um "Bloqueio" de cota)
            $table->foreignId('agenda_hospede_id')->nullable()->constrained('agenda_hospedes')->onDelete('set null');

            // Para informar "RESERVADO OSASCO" ou qualquer cota manual que não possua hospede cadastrado
            $table->string('bloqueio_nota')->nullable();

            // fila_espera, reservado, confirmado, cancelado
            $table->string('status')->default('reservado');

            // Caso seja fila de espera, qual as acomodações de preferência ou ordem de fila
            $table->integer('ordem_fila')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_reservas');
    }
};
