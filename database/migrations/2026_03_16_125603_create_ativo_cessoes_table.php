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
        Schema::create('ativo_cessoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('ativo_usuarios');
            $table->dateTime('data_cessao');
            $table->string('codigo_cessao')->unique();
            $table->string('termo_pdf_path')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ativo_cessoes');
    }
};
