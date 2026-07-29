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
        Schema::table('protocolos', function (Blueprint $table) {
            if (!Schema::hasColumn('protocolos', 'tipo_escopo')) {
                $table->string('tipo_escopo', 20)->default('individual')->after('canal');
            }
        });

        Schema::table('protocolo_destinatarios', function (Blueprint $table) {
            if (!Schema::hasColumn('protocolo_destinatarios', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('protocolo_id')->constrained('empresas')->nullOnDelete();
            }
            if (!Schema::hasColumn('protocolo_destinatarios', 'cliente_id')) {
                $table->foreignId('cliente_id')->nullable()->after('empresa_id')->constrained('clientes')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocolo_destinatarios', function (Blueprint $table) {
            if (Schema::hasColumn('protocolo_destinatarios', 'cliente_id')) {
                $table->dropForeign(['cliente_id']);
                $table->dropColumn('cliente_id');
            }
            if (Schema::hasColumn('protocolo_destinatarios', 'empresa_id')) {
                $table->dropForeign(['empresa_id']);
                $table->dropColumn('empresa_id');
            }
        });

        Schema::table('protocolos', function (Blueprint $table) {
            if (Schema::hasColumn('protocolos', 'tipo_escopo')) {
                $table->dropColumn('tipo_escopo');
            }
        });
    }
};
