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
        Schema::table('ativo_usuarios', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->after('telefone');
            $table->string('endereco')->nullable()->after('cpf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_usuarios', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'endereco']);
        });
    }
};
