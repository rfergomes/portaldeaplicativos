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
        Schema::table('socio_caixas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('data_pagamento')->constrained('users');
            $table->text('observacao')->nullable()->after('user_id');
        });

        Schema::create('socio_caixa_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_caixa_id')->constrained('socio_caixas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('acao');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socio_caixa_historicos');
        Schema::table('socio_caixas', function (Blueprint $table) {
            if (Schema::hasColumn('socio_caixas', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn(['user_id', 'observacao']);
            }
        });
    }
};
