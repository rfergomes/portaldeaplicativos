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
        Schema::create('agenda_periodos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao'); // Ex: "08.01.2026 à 12.01.2026", "2ª Semana Janeiro-2026"
            $table->date('data_inicial');
            $table->date('data_final');
            $table->date('data_limite')->nullable(); // Pagamento / Confirmação
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_periodos');
    }
};
