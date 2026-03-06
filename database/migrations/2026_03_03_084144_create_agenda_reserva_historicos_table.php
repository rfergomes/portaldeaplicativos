<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agenda_reserva_historicos', function (Blueprint $table) {
            $table->id();

            // Referências desnormalizadas (mantidas mesmo após exclusão)
            $table->unsignedBigInteger('colonia_id')->nullable();
            $table->string('colonia_nome', 100);

            $table->unsignedBigInteger('periodo_id')->nullable();
            $table->string('periodo_descricao', 100);
            $table->date('periodo_data_inicial')->nullable();
            $table->date('periodo_data_final')->nullable();

            // Dados do hóspede (desnormalizados)
            $table->string('hospede_nome', 255)->nullable();
            $table->string('hospede_telefone', 50)->nullable();
            $table->string('hospede_email', 255)->nullable();

            // Acomodação
            $table->string('acomodacao_identificador', 50)->nullable();
            $table->string('acomodacao_tipo', 50)->nullable();

            // Status original da reserva excluída
            $table->string('status_reserva', 30)->nullable();
            $table->string('bloqueio_nota', 255)->nullable();

            // Motivo da exclusão
            $table->text('motivo');

            // Quem excluiu
            $table->unsignedBigInteger('excluido_por')->nullable();
            $table->string('excluido_por_nome', 100)->nullable();

            $table->timestamps();

            $table->index('colonia_id');
            $table->index('periodo_id');
            $table->index('excluido_por');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_reserva_historicos');
    }
};
