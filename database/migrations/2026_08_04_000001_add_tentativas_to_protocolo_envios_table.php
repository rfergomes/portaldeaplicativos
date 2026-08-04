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
        Schema::table('protocolo_envios', function (Blueprint $table) {
            if (!Schema::hasColumn('protocolo_envios', 'tentativas')) {
                $table->integer('tentativas')->default(1)->after('status');
            }
            if (!Schema::hasColumn('protocolo_envios', 'bloqueado_reenvio')) {
                $table->boolean('bloqueado_reenvio')->default(false)->after('tentativas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocolo_envios', function (Blueprint $table) {
            $table->dropColumn(['tentativas', 'bloqueado_reenvio']);
        });
    }
};
