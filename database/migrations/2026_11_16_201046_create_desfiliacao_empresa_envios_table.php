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
        Schema::create('desfiliacao_empresa_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desfiliacao_id')->constrained('desfiliacoes')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('email', 191);
            $table->string('nome_contato', 191)->nullable();
            $table->string('aronline_id', 100)->nullable()->index('idx_desf_emp_envio_aronline');
            $table->enum('status', ['enviado', 'entregue', 'lido', 'falha'])->default('enviado');
            $table->text('resposta_webhook')->nullable();
            $table->timestamp('notificada_em')->nullable();
            $table->timestamp('entregue_em')->nullable();
            $table->timestamp('lido_em')->nullable();
            $table->timestamps();

            $table->index(['desfiliacao_id', 'status'], 'idx_desf_emp_envio_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desfiliacao_empresa_envios');
    }
};
