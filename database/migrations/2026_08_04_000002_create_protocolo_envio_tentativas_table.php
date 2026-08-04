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
        Schema::create('protocolo_envio_tentativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocolo_envio_id')->constrained('protocolo_envios')->onDelete('cascade');
            $table->integer('numero_tentativa');
            $table->string('status_resultado', 50);
            $table->text('resposta_api')->nullable();
            $table->foreignId('executado_por_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolo_envio_tentativas');
    }
};
