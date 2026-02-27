<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('entidade');
            $table->string('operacao');
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();
            $table->timestamp('ocorreu_em');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_eventos');
    }
};

