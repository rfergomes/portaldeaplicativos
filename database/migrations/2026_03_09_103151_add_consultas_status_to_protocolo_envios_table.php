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
        Schema::table('protocolo_envios', function (Blueprint $table) {
            $table->integer('consultas_status')->default(0)->after('status')->comment('Qtd de vezes que a API foi consultada para esse envio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocolo_envios', function (Blueprint $table) {
            $table->dropColumn('consultas_status');
        });
    }
};
