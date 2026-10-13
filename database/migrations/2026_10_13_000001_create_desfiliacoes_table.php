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
        Schema::create('desfiliacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo', 191);
            $table->string('cpf', 14)->index('idx_desfiliacoes_cpf');
            $table->string('matricula', 50)->nullable();
            $table->string('empresa', 191);
            $table->string('email', 191);
            $table->string('telefone', 20);
            $table->text('motivo')->nullable();
            $table->enum('status', ['pendente', 'confirmada', 'expirada', 'cancelada'])
                ->default('pendente')
                ->index('idx_desfiliacoes_status');
            $table->string('ip_solicitacao', 45)->nullable();
            $table->string('ip_confirmacao', 45)->nullable();
            $table->text('user_agent_confirmacao')->nullable();
            $table->timestamp('email_verificado_em')->nullable();
            $table->timestamp('confirmado_em')->nullable();
            $table->longText('assinatura_base64')->nullable();
            $table->string('termo_hash', 64)->nullable()->unique('unq_desfiliacoes_termo_hash');
            $table->string('pdf_path', 255)->nullable();
            $table->timestamps();

            $table->index('created_at', 'idx_desfiliacoes_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desfiliacoes');
    }
};
