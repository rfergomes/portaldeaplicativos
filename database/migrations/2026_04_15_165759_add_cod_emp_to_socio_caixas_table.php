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
            $table->string('cod_emp')->nullable()->after('tipo_socio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socio_caixas', function (Blueprint $table) {
            $table->dropColumn('cod_emp');
        });
    }
};
