<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AtivoDepartamento;
use App\Models\AtivoFornecedor;
use App\Models\AtivoUsuario;
use App\Models\AtivoEquipamento;
use App\Models\AtivoMovimentacao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportLegacyAssetData extends Command
{
    protected $signature = 'app:import-legacy-asset-data';
    protected $description = 'Importa dados legados de arquivos CSV na raiz do projeto';

    public function handle()
    {
        $this->info('Iniciando importação de dados legados...');

        DB::beginTransaction();

        try {
            // 1. Departamentos
            $this->importDepartamentos();

            // 2. Fornecedores
            $this->importFornecedores();

            // 3. Usuários (Cessionários)
            $this->importUsuarios();

            // 4. Equipamentos
            $this->importEquipamentos();

            // 5. Movimentações
            $this->importMovimentacoes();

            DB::commit();
            $this->info('Importação concluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erro na importação: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    private function getCsvData($filename)
    {
        $path = base_path($filename);
        if (!file_exists($path)) {
            $this->warn("Arquivo não encontrado: $filename");
            return [];
        }

        $content = file_get_contents($path);
        // Remove BOM if present
        $content = str_replace("\xEF\xBB\xBF", '', $content);
        
        $lines = explode("\n", str_replace("\r", '', $content));
        $lines = array_filter($lines);
        
        $headerLine = array_shift($lines);
        $header = str_getcsv($headerLine, ';');
        // Clean header keys (remove possible spaces or quotes)
        $header = array_map(function($h) {
            return trim($h, " \t\n\r\0\x0B\"");
        }, $header);
        
        $data = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line, ';');
            if (count($header) === count($row)) {
                $data[] = array_combine($header, $row);
            }
        }
        return $data;
    }

    private function parseDate($dateStr)
    {
        if (empty($dateStr)) return null;
        try {
            // Tenta lidar com formatos comuns
            if (strpos($dateStr, '/') !== false) {
                $parts = explode(' ', $dateStr);
                $datePart = $parts[0];
                $timePart = isset($parts[1]) ? $parts[1] : '00:00:00';
                
                return Carbon::createFromFormat('d/m/Y H:i', $datePart . ' ' . $timePart)->format('Y-m-d H:i:s');
            }
            return Carbon::parse($dateStr)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function importDepartamentos()
    {
        $this->info('Importando departamentos...');
        $rows = $this->getCsvData('ImportarDepartamentos.csv');
        foreach ($rows as $row) {
            AtivoDepartamento::updateOrCreate(
                ['nome' => $row['Nome_Setor']],
                ['ativo' => true]
            );
        }
    }

    private function importFornecedores()
    {
        $this->info('Importando fornecedores...');
        $rows = $this->getCsvData('ImportarFornecedores.csv');
        foreach ($rows as $row) {
            AtivoFornecedor::updateOrCreate(
                ['nome' => $row['Nome_Fantasia']],
                [
                    'cnpj' => $row['CNPJ'] ?? null,
                    'email' => $row['Contato_Email'] ?? null,
                    'telefone' => $row['Contato_Telefone'] ?? null,
                    'contato' => $row['Contato_Nome'] ?? null,
                    'ativo' => true
                ]
            );
        }
    }

    private function importUsuarios()
    {
        $this->info('Importando usuários (cessionários)...');
        $rows = $this->getCsvData('ImportarCessionarios.csv');
        foreach ($rows as $row) {
            AtivoUsuario::updateOrCreate(
                ['email' => $row['Email'] ?: ($row['ID_Cessionario'] . '@legado.com')],
                [
                    'nome' => $row['Nome_Completo'],
                    'telefone' => $row['Telefone'] ?? null,
                    'ativo' => true
                ]
            );
        }
    }

    private function importEquipamentos()
    {
        $this->info('Importando equipamentos...');
        $rows = $this->getCsvData('ImportarEquipamentos.csv');
        foreach ($rows as $row) {
            // Tenta encontrar o fornecedor
            $fornecedor = AtivoFornecedor::where('nome', 'LIKE', '%' . ($row['ID_Fornecedor'] ?? '') . '%')->first();
            if (!$fornecedor && !empty($row['ID_Fornecedor'])) {
                // Caso o CSV use o ID_Fornecedor FRN01 etc, precisamos de um mapeamento ou busca
                // Como não tenho o mapeamento exato ID -> Nome, vou tentar buscar por padrão FRNxx se necessário
            }

            $statusMap = [
                'Em uso' => 'em_uso',
                'Disponível' => 'disponivel',
                'Manutenção' => 'manutencao',
                'Baixado' => 'baixado'
            ];

            AtivoEquipamento::updateOrCreate(
                ['identificador' => $row['ID_Equipamento']],
                [
                    'descricao' => $row['Descricao'],
                    'numero_serie' => $row['Numero_Serie'] ?? null,
                    'fornecedor_id' => $fornecedor?->id,
                    'data_compra' => $this->parseDate($row['Data_Compra']),
                    'status' => $statusMap[$row['Status']] ?? 'em_uso',
                    'tipo_uso' => $row['Tipo_Uso_Atual'] ?? null,
                    'localizacao_atual' => $row['Localizacao_Atual'] ?? 'Estoque',
                    'observacao' => $row['Observação'] ?? null,
                ]
            );
        }
    }

    private function importMovimentacoes()
    {
        $this->info('Importando movimentações...');
        $rows = $this->getCsvData('ImportarMovimentacoes.csv');
        $responsavel = User::first(); // Usando o primeiro usuário como responsável padrão se não encontrar

        foreach ($rows as $row) {
            $equipamento = AtivoEquipamento::where('identificador', $row['ID_Equipamento'])->first();
            if (!$equipamento) continue;

            $usuario = null;
            if (!empty($row['Destino']) && strpos($row['Destino'], 'CES') === 0) {
                // Tenta mapear o destino (CESxxx) para o usuário importado
                // Como salvamos o email com ID_Cessionario + @legado.com se vazio, facilitamos a busca
                $usuario = AtivoUsuario::where('email', 'LIKE', $row['Destino'] . '%')->first();
            }

            AtivoMovimentacao::updateOrCreate(
                ['observacao' => 'ID Legado: ' . $row['ID_Movimentacao']],
                [
                    'equipamento_id' => $equipamento->id,
                    'usuario_id' => $usuario?->id,
                    'tipo' => strpos($row['Tipo_Movimentacao'], 'Cessão') !== false ? 'cessao' : 'transferencia',
                    'data_movimentacao' => $this->parseDate($row['Data_Movimentacao']) ?? now(),
                    'origem' => $row['Origem'] ?? 'Estoque',
                    'destino' => $row['Destino'] ?? null,
                    'responsavel_id' => $responsavel->id
                ]
            );
        }
    }
}
