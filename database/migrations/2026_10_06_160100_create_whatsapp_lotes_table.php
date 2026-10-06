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
        Schema::create('whatsapp_lotes', function (Blueprint $table) {
            $table->id();
            $table->enum('modulo', ['caixa', 'folha']);
            $table->foreignId('whatsapp_template_id')->constrained('whatsapp_templates');
            $table->foreignId('user_id')->constrained('users');
            $table->json('filtros_aplicados');
            $table->json('parametros_extras')->nullable();
            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->unsignedInteger('total_enviados')->default(0);
            $table->unsignedInteger('total_falhas')->default(0);
            $table->enum('status', ['pendente', 'processando', 'concluido', 'erro_parcial', 'cancelado'])->default('pendente');
            $table->text('mensagem_erro')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_lotes');
    }
};
