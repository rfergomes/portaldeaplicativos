<?php

declare(strict_types=1);

namespace App\Domain\Whatsapp\DTOs;

readonly class WhatsappLotePayloadDTO
{
    /**
     * @param string $modulo 'caixa' ou 'folha'
     * @param int $templateId ID do WhatsappTemplate
     * @param int $userId ID do usuário operador
     * @param array<string, mixed> $filtros Filtros aplicados na listagem
     * @param array<int, string> $parametrosExtras Parâmetros informados na modal
     */
    public function __construct(
        public string $modulo,
        public int $templateId,
        public int $userId,
        public array $filtros,
        public array $parametrosExtras = []
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            modulo: (string) ($data['modulo'] ?? 'caixa'),
            templateId: (int) ($data['whatsapp_template_id'] ?? $data['template_id']),
            userId: $userId,
            filtros: (array) ($data['filtros'] ?? []),
            parametrosExtras: (array) ($data['parametros_extras'] ?? [])
        );
    }
}
