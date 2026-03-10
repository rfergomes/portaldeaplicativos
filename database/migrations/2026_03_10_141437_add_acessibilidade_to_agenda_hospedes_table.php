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
        Schema::table('agenda_hospedes', function (Blueprint $table) {
            $table->boolean('acessibilidade')->default(false)->after('empresa_livre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_hospedes', function (Blueprint $table) {
            $table->dropColumn('acessibilidade');
        });
    }
};
