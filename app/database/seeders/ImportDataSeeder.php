<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportDataSeeder extends Seeder
{
    public function run()
    {
        $this->importEmpresas();
        $this->importContatos();
    }

    private function importEmpresas()
    {
        $path = base_path('ImportarEmpresas.csv');
        if (!file_exists($path)) {
            $this->command->error("Arquivo ImportarEmpresas.csv não encontrado em $path");
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file, 0, ';');
        if ($header) {
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        }

        $count = 0;
        $errors = 0;
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($header) !== count($row)) {
                continue;
            }

            $data = array_combine($header, $row);

            try {
                $cnpj = $this->formatCnpj($data['cnpj']);
                $createdAt = $this->parseDate($data['created_at']);

                $nomeCurto = $data['nome_curto'] ?? '';
                if (empty($nomeCurto)) {
                    $parts = explode(' ', trim($data['razao_social']));
                    $nomeCurto = implode(' ', array_slice($parts, 0, 2));
                }

                $regiaoId = (int) $data['regiao_id'] ?: 7;

                Empresa::updateOrCreate(
                    ['id' => (int) $data['id']],
                    [
                        'regiao_id' => $regiaoId,
                        'razao_social' => mb_substr(mb_strtoupper($data['razao_social'], 'UTF-8'), 0, 255),
                        'nome_fantasia' => mb_substr(mb_strtoupper($data['nome_fantasia'], 'UTF-8'), 0, 255),
                        'nome_curto' => mb_substr(mb_strtoupper($nomeCurto, 'UTF-8'), 0, 255),
                        'cnpj' => $cnpj,
                        'empresa_erp' => mb_substr($data['empresa_erp'], 0, 50),
                        'inscricao_estadual' => mb_substr($data['inscricao_estadual'], 0, 50),
                        'email' => mb_substr($data['email'], 0, 255),
                        'telefone' => mb_substr($data['telefone'], 0, 50),
                        'cidade' => mb_substr(mb_strtoupper($data['cidade'], 'UTF-8'), 0, 255),
                        'estado' => mb_substr(mb_strtoupper($data['estado'], 'UTF-8'), 0, 2),
                        'categoria' => mb_substr(mb_strtoupper($data['categoria'], 'UTF-8'), 0, 255),
                        'ativo' => (int) ($data['ativo'] ?? 0),
                        'created_at' => $createdAt,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $errors++;
                $this->command->error("Erro empresa ID " . ($data['id'] ?? 'unknown') . ": " . $e->getMessage());
                Log::error("Erro importação empresa", ['id' => $data['id'], 'error' => $e->getMessage()]);
            }
        }
        fclose($file);
        $this->command->info("$count empresas processadas. $errors erros.");
    }

    private function importContatos()
    {
        $path = base_path('ImportarContatos.csv');
        if (!file_exists($path)) {
            $this->command->error("Arquivo ImportarContatos.csv não encontrado em $path");
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file, 0, ';');
        if ($header) {
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        }

        $count = 0;
        $errors = 0;
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($header) !== count($row)) {
                continue;
            }

            $data = array_combine($header, $row);

            try {
                if (!Empresa::find($data['empresa_id'])) {
                    continue;
                }

                Cliente::create([
                    'empresa_id' => (int) $data['empresa_id'],
                    'tipo_cliente_id' => (int) ($data['tipo_cliente_id'] ?: 1),
                    'nome' => mb_substr(mb_strtoupper($data['nome'] ?: 'CONTATO', 'UTF-8'), 0, 255),
                    'documento' => mb_substr($data['documento'], 0, 50),
                    'email' => mb_substr($data['email'], 0, 255),
                    'telefone' => mb_substr($data['telefone'], 0, 50),
                    'cidade' => mb_substr(mb_strtoupper($data['cidade'], 'UTF-8'), 0, 255),
                    'estado' => mb_substr(mb_strtoupper($data['estado'], 'UTF-8'), 0, 2),
                    'ativo' => (int) ($data['ativo'] ?? 1),
                ]);
                $count++;
            } catch (\Exception $e) {
                $errors++;
                Log::error("Erro importação contato", ['empresa_id' => $data['empresa_id'], 'error' => $e->getMessage()]);
            }
        }
        fclose($file);
        $this->command->info("$count contatos processados. $errors erros.");
    }

    private function formatCnpj($val)
    {
        if (empty($val))
            return null;
        if (str_contains(strtoupper($val), 'E+')) {
            $val = str_replace(',', '.', $val);
            $val = sprintf("%.0f", (float) $val);
        }
        $clean = preg_replace('/\D/', '', $val);
        return $clean ? str_pad($clean, 14, '0', STR_PAD_LEFT) : null;
    }

    private function parseDate($val)
    {
        if (empty($val))
            return now();
        try {
            // Tenta múltiplos formatos comuns
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $val)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    continue;
                }
            }
            return now();
        } catch (\Exception $e) {
            return now();
        }
    }
}
