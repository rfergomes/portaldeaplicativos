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
            $table->foreignId('fornecedor_id')->nullable()->after('fabricante_id')->constrained('ativo_fornecedores')->nullOnDelete();
            $table->string('numero_nf')->nullable()->after('fornecedor_id');
            $table->date('data_aquisicao')->nullable()->after('numero_nf');
            $table->decimal('valor_total', 12, 2)->nullable()->after('data_aquisicao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_licencas', function (Blueprint $table) {
            $table->dropForeign(['fornecedor_id']);
            $table->dropColumn(['fornecedor_id', 'numero_nf', 'data_aquisicao', 'valor_total']);
        });
    }
};
