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
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->boolean('is_depreciavel')->default(true)->after('valor_item');
            $table->decimal('valor_residual', 15, 2)->default(1.00)->after('is_depreciavel');
            $table->integer('vida_util_meses')->default(60)->after('valor_residual');
            $table->string('metodo_depreciacao')->default('linear')->after('vida_util_meses');
            $table->string('categoria_depreciacao')->nullable()->after('metodo_depreciacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ativo_equipamentos', function (Blueprint $table) {
            $table->dropColumn([
                'is_depreciavel',
                'valor_residual',
                'vida_util_meses',
                'metodo_depreciacao',
                'categoria_depreciacao'
            ]);
        });
    }
};
