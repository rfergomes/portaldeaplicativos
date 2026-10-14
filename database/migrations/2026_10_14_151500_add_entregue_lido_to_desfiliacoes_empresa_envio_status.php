<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN empresa_envio_status ENUM('pendente_contato', 'enviado', 'entregue', 'lido', 'falha', 'nao_aplicavel') NULL DEFAULT 'nao_aplicavel'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN empresa_envio_status ENUM('pendente_contato', 'enviado', 'falha', 'nao_aplicavel') NULL DEFAULT 'nao_aplicavel'");
        }
    }
};
