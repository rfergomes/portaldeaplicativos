<?php

declare(strict_types=1);

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
        Schema::create('whatsapp_lote_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_lote_id')->constrained('whatsapp_lotes')->cascadeOnDelete();
            $table->string('matricula', 50)->nullable()->index();
            $table->unsignedBigInteger('socio_folha_id')->nullable()->index();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->string('destinatario_nome', 255);
            $table->string('telefone', 25);
            $table->enum('status', ['pendente', 'enviado', 'falha'])->default('pendente');
            $table->string('notification_id', 100)->nullable();
            $table->text('motivo_falha')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_lote_itens');
    }
};
