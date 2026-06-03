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
        Schema::create('socio_folha_email_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_folha_id')->constrained('socios_folha')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('email_destinatario');
            $table->string('assunto');
            $table->string('tipo_envio');
            $table->string('status')->default('ENVIADO'); // ENVIADO, ABERTO, BOUNCE
            $table->string('bounce_code')->nullable();
            $table->text('bounce_description')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socio_folha_email_historicos');
    }
};
