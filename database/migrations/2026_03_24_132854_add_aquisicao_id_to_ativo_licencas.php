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
        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->foreignId('aquisicao_id')->nullable()->after('id')->constrained('ativo_aquisicoes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->dropForeign(['aquisicao_id']);
            $table->dropColumn('aquisicao_id');
        });
    }
};
