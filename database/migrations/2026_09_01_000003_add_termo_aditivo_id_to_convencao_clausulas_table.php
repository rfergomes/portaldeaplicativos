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
        Schema::table('convencao_clausulas', function (Blueprint $table) {
            $table->foreignId('convencao_termo_aditivo_id')
                  ->nullable()
                  ->after('convencao_coletiva_id')
                  ->constrained('convencao_termos_aditivos')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convencao_clausulas', function (Blueprint $table) {
            $table->dropForeign(['convencao_termo_aditivo_id']);
            $table->dropColumn('convencao_termo_aditivo_id');
        });
    }
};
