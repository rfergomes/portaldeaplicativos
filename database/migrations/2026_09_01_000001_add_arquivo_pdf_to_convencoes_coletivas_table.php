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
        Schema::table('convencoes_coletivas', function (Blueprint $table) {
            $table->string('arquivo_pdf')->nullable()->after('abrangencia');
            $table->string('arquivo_nome_original')->nullable()->after('arquivo_pdf');
            $table->unsignedBigInteger('arquivo_tamanho')->nullable()->after('arquivo_nome_original');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convencoes_coletivas', function (Blueprint $table) {
            $table->dropColumn(['arquivo_pdf', 'arquivo_nome_original', 'arquivo_tamanho']);
        });
    }
};
