<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agenda_inscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colonia_id')->constrained('colonias')->cascadeOnDelete();
            $table->foreignId('agenda_periodo_id')->constrained('agenda_periodos')->cascadeOnDelete();
            $table->foreignId('agenda_hospede_id')->nullable()->constrained('agenda_hospedes')->nullOnDelete();

            // Status do resultado do sorteio
            $table->enum('status', ['pendente', 'sorteado', 'espera', 'cancelado'])->default('pendente');
            $table->unsignedInteger('ordem_espera')->nullable()->comment('Posição na fila de espera');

            // Ao ser sorteado, qual acomodação ele ganhou
            $table->foreignId('acomodacao_id')->nullable()->constrained('colonia_acomodacaos')->nullOnDelete();

            $table->text('observacao')->nullable();

            // Referência à reserva criada automaticamente após o sorteio
            $table->foreignId('reserva_id')->nullable()->constrained('agenda_reservas')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_inscricoes');
    }
};
