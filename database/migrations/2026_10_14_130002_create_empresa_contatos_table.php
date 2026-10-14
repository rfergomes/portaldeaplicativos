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
        Schema::create('empresa_contatos', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj', 18)->nullable()->index('idx_empresa_contatos_cnpj');
            $table->string('razao_social', 191)->index('idx_empresa_contatos_razao_social');
            $table->string('nome_fantasia', 191)->nullable();
            $table->string('email_rh', 191)->index('idx_empresa_contatos_email_rh');
            $table->string('telefone_rh', 50)->nullable();
            $table->string('responsavel_rh', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_contatos');
    }
};
