<?php

declare(strict_types=1);

namespace App\Domain\Whatsapp\DTOs;

readonly class KwikSyncResultDTO
{
    /**
     * @param array<int, string> $erros
     */
    public function __construct(
        public int $totalEncontrados = 0,
        public int $totalImportados = 0,
        public int $totalAtualizados = 0,
        public int $totalAprovados = 0,
        public int $totalRejeitados = 0,
        public array $erros = []
    ) {}

    public function resumoMensagem(): string
    {
        if (!empty($this->erros) && $this->totalEncontrados === 0) {
            return 'Falha ao sincronizar: ' . implode('; ', $this->erros);
        }

        return sprintf(
            'Sincronização concluída: %d template(s) retornado(s) pela Kwik (%d novos, %d atualizados). Aprovados: %d, Rejeitados/Pendentes: %d.',
            $this->totalEncontrados,
            $this->totalImportados,
            $this->totalAtualizados,
            $this->totalAprovados,
            $this->totalRejeitados
        );
    }
}
