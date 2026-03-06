<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Evento;
use App\Models\Convite;
use App\Models\Convidado;
use App\Models\LoteConvite;
use Illuminate\Support\Facades\DB;

class ImportLegacyEvents extends Command
{
    protected $signature = 'app:import-legacy-events';
    protected $description = 'Importa eventos, convites e convidados de arquivos CSV';

    public function handle()
    {
        $this->info("Iniciando processo de importação via CSV...");

        // 1. Limpeza
        $this->warn("Limpando dados legados existentes...");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = ['convidados', 'vendas_convite', 'convites', 'lotes_convite', 'eventos'];
        foreach ($tables as $table) {
            DB::table($table)->delete();
            $this->info("Tabela $table limpa.");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->info("Limpeza concluída.");

        $eventMapping = []; // [OldID => NewID]
        $inviteMapping = []; // [OldID => NewID]

        // 2. Eventos
        try {
            if (!$this->importEventos($eventMapping))
                return 1;
        } catch (\Exception $e) {
            $this->error("Erro crítico na importação de eventos: " . $e->getMessage());
            return 1;
        }

        // 3. Convites
        try {
            if (!$this->importConvites($eventMapping, $inviteMapping))
                return 1;
        } catch (\Exception $e) {
            $this->error("Erro crítico na importação de convites: " . $e->getMessage());
            return 1;
        }

        // 4. Convidados
        try {
            if (!$this->importConvidados($inviteMapping))
                return 1;
        } catch (\Exception $e) {
            $this->error("Erro crítico na importação de convidados: " . $e->getMessage());
            return 1;
        }

        $this->info("Processo de importação concluído com sucesso!");
        return 0;
    }

    private function importEventos(&$mapping)
    {
        $file = base_path('ImportarEventos.csv');
        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: $file");
            return false;
        }

        $f = fopen($file, 'r');
        $header = fgetcsv($f, 0, ';');

        $this->info("Importando eventos...");
        $count = 0;
        while (($row = fgetcsv($f, 0, ';')) !== FALSE) {
            if (empty($row[1]))
                continue;

            try {
                $evento = Evento::create([
                    'nome' => $row[1],
                    'data_inicio' => $this->parseDate($row[2]),
                    'data_fim' => $this->parseDate($row[3] ?: $row[2]),
                    'local' => $row[4] ?: 'Não informado',
                    'valor_inteira' => $this->parseMoney($row[5] ?? '0'),
                    'valor_meia' => $this->parseMoney($row[6] ?? '0'),
                    'encerrado' => ($row[7] ?? 0) == 1
                ]);

                $oldId = $this->cleanBOM($row[0]);
                $mapping[$oldId] = $evento->id;
                $count++;
            } catch (\Exception $e) {
                $this->warn("Erro ao importar evento '{$row[1]}': " . $e->getMessage());
            }
        }
        fclose($f);
        $this->info("Eventos importados: $count");
        return true;
    }

    private function importConvites($eventMapping, &$inviteMapping)
    {
        $file = base_path('ImportarConvites.csv');
        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: $file");
            return false;
        }

        $f = fopen($file, 'r');
        $header = fgetcsv($f, 0, ';');

        $this->info("Importando convites...");
        $count = 0;
        while (($row = fgetcsv($f, 0, ';')) !== FALSE) {
            if (empty($row[3]))
                continue; // codigo

            $oldEventId = $row[1];
            $newEventId = $eventMapping[$oldEventId] ?? null;

            if (!$newEventId)
                continue;

            try {
                $lote = LoteConvite::firstOrCreate(
                    ['evento_id' => $newEventId, 'nome' => 'Lote Único (Legado)'],
                    ['quantidade_total' => 9999, 'quantidade_disponivel' => 9999]
                );

                $convite = Convite::updateOrCreate(
                    ['codigo' => substr((string) $row[3], 0, 50)],
                    [
                        'evento_id' => $newEventId,
                        'lote_id' => $lote->id,
                        'nome_responsavel' => substr((string) $row[2], 0, 100),
                        'tipo' => is_numeric($row[4]) ? intval($row[4]) : 1,
                        'placa' => substr((string) ($row[5] ?? ''), 0, 15),
                        'empresa' => substr((string) ($row[6] ?? ''), 0, 100),
                        'valor' => $this->parseMoney($row[7] ?? '0'),
                        'utilizado' => ($row[8] ?? 0) == 1
                    ]
                );

                $inviteMapping[$row[0]] = $convite->id;
                $count++;
            } catch (\Exception $e) {
                $this->warn("Erro ao importar convite cod '{$row[3]}': " . $e->getMessage());
            }
        }
        fclose($f);
        $this->info("Convites importados: $count");
        return true;
    }

    private function importConvidados($inviteMapping)
    {
        $file = base_path('ImportarConvidados.csv');
        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: $file");
            return false;
        }

        $f = fopen($file, 'r');
        $header = fgetcsv($f, 0, ';');

        $this->info("Importando convidados...");
        $count = 0;
        while (($row = fgetcsv($f, 0, ';')) !== FALSE) {
            if (empty($row[2]))
                continue; // nome

            $oldInviteId = $row[1];
            $newInviteId = $inviteMapping[$oldInviteId] ?? null;

            if (!$newInviteId)
                continue;

            try {
                Convidado::create([
                    'convite_id' => $newInviteId,
                    'nome' => substr((string) $row[2], 0, 100),
                    'documento' => substr((string) ($row[3] ?? ''), 0, 20),
                    'empresa' => substr((string) ($row[4] ?? ''), 0, 100),
                    'valor' => $this->parseMoney($row[5] ?? '0'),
                    'presente' => ($row[6] ?? 0) == 1
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->warn("Erro ao importar convidado '{$row[2]}': " . $e->getMessage());
            }
        }
        fclose($f);
        $this->info("Convidados importados: $count");
        return true;
    }

    private function parseDate($value)
    {
        if (empty($value))
            return Carbon::now();
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    private function parseMoney($value)
    {
        $clean = str_replace(['R$', ' ', '.'], '', $value);
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    private function cleanBOM($value)
    {
        return str_replace("\xEF\xBB\xBF", '', $value);
    }
}
