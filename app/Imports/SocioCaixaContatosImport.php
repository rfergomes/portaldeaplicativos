<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\SocioCaixa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SocioCaixaContatosImport implements ToCollection, WithHeadingRow
{
    private int $totalLinhas = 0;
    private int $atualizados = 0;
    private int $naoEncontrados = 0;
    private int $ignorados = 0;
    private array $matriculasProcessadas = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $this->totalLinhas++;

            $matriculaRaw = $row['codigo'] 
                ?? $row['código'] 
                ?? $row['cod'] 
                ?? $row['matricula'] 
                ?? $row['mat'] 
                ?? $row['matr'] 
                ?? null;

            if ($matriculaRaw === null && isset($row[0])) {
                $matriculaRaw = $row[0];
            }

            if ($matriculaRaw === null) {
                $this->ignorados++;
                continue;
            }

            $matriculaStr = trim((string) $matriculaRaw);
            if ($matriculaStr === '' || $matriculaStr === '-' || strtolower($matriculaStr) === 'null') {
                $this->ignorados++;
                continue;
            }

            // Normaliza caso venha com sufixo decimal float como "30938.0"
            if (str_contains($matriculaStr, '.')) {
                $partes = explode('.', $matriculaStr);
                $matricula = trim($partes[0]);
            } else {
                $matricula = $matriculaStr;
            }

            // Ignora linhas que não sejam numéricas (ex: rodapés com totais)
            if (!is_numeric($matricula)) {
                $this->ignorados++;
                continue;
            }

            // Evita processar a mesma matrícula múltiplas vezes na mesma importação
            if (isset($this->matriculasProcessadas[$matricula])) {
                continue;
            }
            $this->matriculasProcessadas[$matricula] = true;

            $telefoneRaw = $row['telefone_movel'] 
                ?? $row['telefone_móvel'] 
                ?? $row['celular'] 
                ?? $row['telefone'] 
                ?? $row['tel'] 
                ?? $row['whatsapp'] 
                ?? ($row[3] ?? null);

            $emailRaw = $row['e_mail'] 
                ?? $row['email'] 
                ?? $row['correio_eletronico'] 
                ?? ($row[2] ?? null);

            $telefoneFormatado = $this->formatarTelefone($telefoneRaw ? (string) $telefoneRaw : null);
            $emailFormatado = $this->formatarEmail($emailRaw ? (string) $emailRaw : null);

            if (empty($telefoneFormatado) && empty($emailFormatado)) {
                $this->ignorados++;
                continue;
            }

            $dadosUpdate = [];
            if (!empty($telefoneFormatado)) {
                $dadosUpdate['telefone'] = $telefoneFormatado;
            }
            if (!empty($emailFormatado)) {
                $dadosUpdate['email'] = $emailFormatado;
            }

            $matriculaSemZero = ltrim($matricula, '0');
            $query = SocioCaixa::where(function ($q) use ($matricula, $matriculaSemZero) {
                $q->where('matricula', $matricula);
                if (!empty($matriculaSemZero) && $matriculaSemZero !== $matricula) {
                    $q->orWhere('matricula', $matriculaSemZero);
                }
            });

            $afetados = $query->update($dadosUpdate);

            if ($afetados > 0) {
                $this->atualizados++;
            } else {
                $this->naoEncontrados++;
            }
        }
    }

    public function getResumo(): array
    {
        return [
            'total_linhas' => $this->totalLinhas,
            'atualizados' => $this->atualizados,
            'nao_encontrados' => $this->naoEncontrados,
            'ignorados' => $this->ignorados,
            'matriculas_unicas' => count($this->matriculasProcessadas),
        ];
    }

    protected function formatarTelefone(?string $telefone): ?string
    {
        if (empty($telefone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $telefone);
        if (empty($digits)) {
            return null;
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits);
        }

        $trimmed = trim($telefone);
        return !empty($trimmed) ? $trimmed : null;
    }

    protected function formatarEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        $emailLimpo = strtolower(trim($email));
        if (filter_var($emailLimpo, FILTER_VALIDATE_EMAIL)) {
            return $emailLimpo;
        }

        return null;
    }
}
