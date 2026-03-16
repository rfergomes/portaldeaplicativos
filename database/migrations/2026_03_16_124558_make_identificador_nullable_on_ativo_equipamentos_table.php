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
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->string('identificador')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->string('identificador')->nullable(false)->change();
        });
    }
};
