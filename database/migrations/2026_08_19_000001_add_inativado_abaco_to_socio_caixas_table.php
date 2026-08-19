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
            $table->boolean('inativado_abaco')->default(false)->after('motivo_postergacao');
            $table->timestamp('inativado_abaco_em')->nullable()->after('inativado_abaco');
            $table->index('inativado_abaco');
            $table->index(['matricula', 'inativado_abaco']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socio_caixas', function (Blueprint $table) {
            $table->dropIndex(['matricula', 'inativado_abaco']);
            $table->dropIndex(['inativado_abaco']);
            $table->dropColumn(['inativado_abaco', 'inativado_abaco_em']);
        });
    }
};
