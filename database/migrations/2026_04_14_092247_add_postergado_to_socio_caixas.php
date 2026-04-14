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
        Schema::table('socio_caixas', function (Blueprint $row) {
            $row->timestamp('postergado_ate')->nullable()->after('data_pagamento');
            $row->text('motivo_postergacao')->nullable()->after('postergado_ate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socio_caixas', function (Blueprint $row) {
            $row->dropColumn(['postergado_ate', 'motivo_postergacao']);
        });
    }
};
