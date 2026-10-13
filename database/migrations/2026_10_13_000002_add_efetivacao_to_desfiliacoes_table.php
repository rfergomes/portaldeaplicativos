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
        Schema::table('desfiliacoes', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN status ENUM('pendente', 'confirmada', 'efetivada', 'expirada', 'cancelada') NOT NULL DEFAULT 'pendente'");
            }

            $table->foreignId('efetivado_por_user_id')
                ->nullable()
                ->after('pdf_path')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('efetivado_em')
                ->nullable()
                ->after('efetivado_por_user_id');

            $table->string('protocolo_abaco', 100)
                ->nullable()
                ->after('efetivado_em');

            $table->text('observacao_efetivacao')
                ->nullable()
                ->after('protocolo_abaco');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desfiliacoes', function (Blueprint $table) {
            $table->dropForeign(['efetivado_por_user_id']);
            $table->dropColumn([
                'efetivado_por_user_id',
                'efetivado_em',
                'protocolo_abaco',
                'observacao_efetivacao',
            ]);

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN status ENUM('pendente', 'confirmada', 'expirada', 'cancelada') NOT NULL DEFAULT 'pendente'");
            }
        });
    }
};
