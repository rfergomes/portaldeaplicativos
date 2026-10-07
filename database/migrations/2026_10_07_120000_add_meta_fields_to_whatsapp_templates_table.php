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
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->string('status_meta', 50)->nullable()->after('ativo');
            $table->string('categoria', 50)->nullable()->after('status_meta');
            $table->string('language', 20)->nullable()->default('pt_BR')->after('categoria');
            $table->string('namespace', 100)->nullable()->after('language');
            $table->string('rejected_reason', 255)->nullable()->after('namespace');
            $table->timestamp('sincronizado_em')->nullable()->after('rejected_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn([
                'status_meta',
                'categoria',
                'language',
                'namespace',
                'rejected_reason',
                'sincronizado_em',
            ]);
        });
    }
};
