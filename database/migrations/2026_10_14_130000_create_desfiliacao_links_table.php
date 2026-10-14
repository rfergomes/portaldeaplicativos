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
        Schema::create('desfiliacao_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique('unq_desfiliacao_links_token');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('destinatario_nome', 191)->nullable();
            $table->string('destinatario_contato', 50)->nullable();
            $table->enum('canal_envio', ['manual', 'whatsapp', 'email', 'kwik'])->default('manual');
            $table->unsignedInteger('horas_validade')->default(48);
            $table->timestamp('expira_em')->index('idx_desfiliacao_links_expira_em');
            $table->boolean('utilizado')->default(false)->index('idx_desfiliacao_links_utilizado');
            $table->timestamp('utilizado_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->foreignId('desfiliacao_id')->nullable()->constrained('desfiliacoes')->nullOnDelete();
            $table->timestamps();

            $table->index(['utilizado', 'expira_em'], 'idx_desfiliacao_links_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desfiliacao_links');
    }
};
