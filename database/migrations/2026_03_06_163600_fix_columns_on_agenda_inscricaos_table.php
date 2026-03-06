<?php
6: 
7: use Illuminate\Database\Migrations\Migration;
8: use Illuminate\Database\Schema\Blueprint;
9: use Illuminate\Support\Facades\Schema;
10: 
11: return new class extends Migration {
12:     public function up(): void
13:     {
14:         Schema::table('agenda_inscricoes', function (Blueprint $table) {
15:             // Renomear apenas se as colunas antigas existirem (caso do VPS)
16:             if (Schema::hasColumn('agenda_inscricoes', 'periodo_id') && !Schema::hasColumn('agenda_inscricoes', 'agenda_periodo_id')) {
17:                 $table->renameColumn('periodo_id', 'agenda_periodo_id');
18:             }
19:             
20:             if (Schema::hasColumn('agenda_inscricoes', 'hospede_id') && !Schema::hasColumn('agenda_inscricoes', 'agenda_hospede_id')) {
21:                 $table->renameColumn('hospede_id', 'agenda_hospede_id');
22:             }
23:         });
24:     }
25: 
26:     public function down(): void
27:     {
28:         Schema::table('agenda_inscricoes', function (Blueprint $table) {
29:             if (Schema::hasColumn('agenda_inscricoes', 'agenda_periodo_id')) {
30:                 $table->renameColumn('agenda_periodo_id', 'periodo_id');
31:             }
32:             
33:             if (Schema::hasColumn('agenda_inscricoes', 'agenda_hospede_id')) {
34:                 $table->renameColumn('agenda_hospede_id', 'hospede_id');
35:             }
36:         });
37:     }
38: };
39: 
