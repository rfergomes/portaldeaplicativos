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
                DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN status ENUM('pendente', 'confirmada', 'recepcionada', 'efetivada', 'expirada', 'cancelada') NOT NULL DEFAULT 'pendente'");
            }

            $table->foreignId('recepcionado_por_user_id')
                ->nullable()
                ->after('pdf_path')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('recepcionado_em')
                ->nullable()
                ->after('recepcionado_por_user_id');

            $table->text('observacao_recepcao')
                ->nullable()
                ->after('recepcionado_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desfiliacoes', function (Blueprint $table) {
            $table->dropForeign(['recepcionado_por_user_id']);
            $table->dropColumn([
                'recepcionado_por_user_id',
                'recepcionado_em',
                'observacao_recepcao',
            ]);

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN status ENUM('pendente', 'confirmada', 'efetivada', 'expirada', 'cancelada') NOT NULL DEFAULT 'pendente'");
            }
        });
    }
};
