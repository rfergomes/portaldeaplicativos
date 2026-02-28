<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('regiao_id')->nullable()->after('id')->constrained('regioes');
            $table->string('empresa_erp')->nullable()->after('cnpj')->comment('ID Legado ERP: EMPRESA');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('regiao_id');
            $table->dropColumn('empresa_erp');
        });
    }
};
