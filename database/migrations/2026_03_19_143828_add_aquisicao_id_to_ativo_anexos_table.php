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
        Schema::table('ativo_anexos', function (Blueprint $table) {
            $table->foreignId('aquisicao_id')->nullable()->after('equipamento_id')->constrained('ativo_aquisicoes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_anexos', function (Blueprint $table) {
            $table->dropForeign(['aquisicao_id']);
            $table->dropColumn('aquisicao_id');
        });
    }
};
