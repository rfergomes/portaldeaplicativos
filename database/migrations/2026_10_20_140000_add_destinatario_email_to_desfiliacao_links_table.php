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
            $table->string('destinatario_email', 191)->nullable()->after('destinatario_contato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desfiliacao_links', function (Blueprint $table) {
            $table->dropColumn('destinatario_email');
        });
    }
};
