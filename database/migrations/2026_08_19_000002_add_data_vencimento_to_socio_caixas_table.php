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
            $table->date('data_vencimento')->nullable()->after('valor');
            $table->index('data_vencimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socio_caixas', function (Blueprint $table) {
            $table->dropIndex(['data_vencimento']);
            $table->dropColumn('data_vencimento');
        });
    }
};
