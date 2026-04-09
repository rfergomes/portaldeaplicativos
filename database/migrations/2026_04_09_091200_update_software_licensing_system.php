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
        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->string('categoria')->nullable()->after('tipo_licenca');
            $table->string('modelo')->nullable()->after('categoria');
        });

        Schema::table('ativo_licenca_equipamento', function (Blueprint $table) {
            $table->integer('quantidade')->default(1)->after('equipamento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_licenca_equipamento', function (Blueprint $table) {
            $table->dropColumn('quantidade');
        });

        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'modelo']);
        });
    }
};
