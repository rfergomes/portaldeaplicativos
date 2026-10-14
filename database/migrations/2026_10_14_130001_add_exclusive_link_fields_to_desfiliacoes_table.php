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
                DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN status ENUM('pendente', 'confirmada', 'recepcionada', 'efetivada', 'expirada', 'cancelada', 'recusada') NOT NULL DEFAULT 'pendente'");
            }

            $table->foreignId('desfiliacao_link_id')
                ->nullable()
                ->after('id')
                ->constrained('desfiliacao_links')
                ->nullOnDelete();

            $table->foreignId('recusado_por_user_id')
                ->nullable()
                ->after('observacao_recepcao')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('recusado_em')
                ->nullable()
                ->after('recusado_por_user_id');

            $table->text('motivo_recusa')
                ->nullable()
                ->after('recusado_em');

            $table->string('empresa_email', 191)
                ->nullable()
                ->after('empresa');

            $table->string('empresa_aronline_id', 100)
                ->nullable()
                ->after('empresa_email')
                ->index('idx_desfiliacoes_aronline_id');

            $table->text('empresa_aronline_resposta')
                ->nullable()
                ->after('empresa_aronline_id');

            $table->timestamp('empresa_notificada_em')
                ->nullable()
                ->after('empresa_aronline_resposta');

            $table->enum('empresa_envio_status', ['pendente_contato', 'enviado', 'falha', 'nao_aplicavel'])
                ->default('nao_aplicavel')
                ->nullable()
                ->after('empresa_notificada_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desfiliacoes', function (Blueprint $table) {
            $table->dropForeign(['desfiliacao_link_id']);
            $table->dropForeign(['recusado_por_user_id']);
            $table->dropIndex('idx_desfiliacoes_aronline_id');

            $table->dropColumn([
                'desfiliacao_link_id',
                'recusado_por_user_id',
                'recusado_em',
                'motivo_recusa',
                'empresa_email',
                'empresa_aronline_id',
                'empresa_aronline_resposta',
                'empresa_notificada_em',
                'empresa_envio_status',
            ]);

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE desfiliacoes MODIFY COLUMN status ENUM('pendente', 'confirmada', 'recepcionada', 'efetivada', 'expirada', 'cancelada') NOT NULL DEFAULT 'pendente'");
            }
        });
    }
};
