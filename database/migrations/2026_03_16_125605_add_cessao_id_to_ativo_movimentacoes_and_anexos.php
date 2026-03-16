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
        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->foreignId('cessao_id')->nullable()->constrained('ativo_cessoes')->onDelete('set null');
        });

        Schema::table('ativo_anexos', function (Blueprint $table) {
            $table->foreignId('cessao_id')->nullable()->constrained('ativo_cessoes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_anexos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cessao_id');
        });

        Schema::table('ativo_movimentacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cessao_id');
        });
    }
};
