<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agenda_periodos', function (Blueprint $table) {
            $table->date('data_sorteio')->nullable()->after('data_final');
            $table->date('data_limite_pagamento')->nullable()->after('data_sorteio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_periodos', function (Blueprint $table) {
            $table->dropColumn(['data_sorteio', 'data_limite_pagamento']);
        });
    }
};
