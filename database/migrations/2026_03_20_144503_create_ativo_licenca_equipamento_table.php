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
        Schema::create('ativo_licenca_equipamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('licenca_id')->constrained('ativo_licencas')->onDelete('cascade');
            $table->foreignId('equipamento_id')->constrained('ativo_equipamentos')->onDelete('cascade');
            $table->timestamp('atribuido_em')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ativo_licenca_equipamento');
    }
};
