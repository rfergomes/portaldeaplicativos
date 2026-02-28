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
        Schema::table('convites', function (Blueprint $table) {
            $table->string('nome_responsavel')->nullable()->after('evento_id');
            $table->string('placa')->nullable()->after('nome_responsavel');
            $table->string('empresa')->nullable()->after('placa');
        });

        Schema::table('convidados', function (Blueprint $table) {
            $table->string('empresa')->nullable()->after('documento');
            $table->decimal('valor', 10, 2)->default(0)->after('empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convites', function (Blueprint $table) {
            $table->dropColumn(['nome_responsavel', 'placa', 'empresa']);
        });

        Schema::table('convidados', function (Blueprint $table) {
            $table->dropColumn(['empresa', 'valor']);
        });
    }
};
