<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ativo_movimentacoes MODIFY COLUMN tipo ENUM('cessao', 'emprestimo', 'manutencao', 'devolucao', 'transferencia', 'baixa') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ativo_movimentacoes MODIFY COLUMN tipo ENUM('cessao', 'emprestimo', 'manutencao', 'devolucao', 'transferencia') NOT NULL");
    }
};
