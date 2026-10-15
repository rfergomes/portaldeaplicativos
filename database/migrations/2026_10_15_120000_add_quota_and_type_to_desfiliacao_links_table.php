<?php

declare(strict_types=1);

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
        Schema::table('desfiliacao_links', function (Blueprint $table) {
            $table->string('tipo_link', 30)->default('individual')->after('canal_envio');
            $table->unsignedInteger('limite_usos')->default(1)->after('tipo_link');
            $table->unsignedInteger('usos_atuais')->default(0)->after('limite_usos');

            $table->index(['expira_em', 'cancelado_em', 'utilizado'], 'idx_desfiliacao_links_expurgo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desfiliacao_links', function (Blueprint $table) {
            $table->dropIndex('idx_desfiliacao_links_expurgo');
            $table->dropColumn(['tipo_link', 'limite_usos', 'usos_atuais']);
        });
    }
};
