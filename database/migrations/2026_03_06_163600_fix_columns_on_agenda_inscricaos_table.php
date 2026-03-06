<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_inscricoes', function (Blueprint $table) {
            // Renomear apenas se as colunas antigas existirem (caso do VPS)
            if (Schema::hasColumn('agenda_inscricoes', 'periodo_id') && !Schema::hasColumn('agenda_inscricoes', 'agenda_periodo_id')) {
                $table->renameColumn('periodo_id', 'agenda_periodo_id');
            }

            if (Schema::hasColumn('agenda_inscricoes', 'hospede_id') && !Schema::hasColumn('agenda_inscricoes', 'agenda_hospede_id')) {
                $table->renameColumn('hospede_id', 'agenda_hospede_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agenda_inscricoes', function (Blueprint $table) {
            if (Schema::hasColumn('agenda_inscricoes', 'agenda_periodo_id')) {
                $table->renameColumn('agenda_periodo_id', 'periodo_id');
            }

            if (Schema::hasColumn('agenda_inscricoes', 'agenda_hospede_id')) {
                $table->renameColumn('agenda_hospede_id', 'hospede_id');
            }
        });
    }
};
