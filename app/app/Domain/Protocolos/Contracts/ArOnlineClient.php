<?php

namespace App\Domain\Protocolos\Contracts;

use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;

interface ArOnlineClient
{
    public function send(ArOnlineSendPayload $payload): string;

    public function getEmailStatus(string $idEmail): array;

    public function getFullStatus(string $idEmail): array;

    public function getReceiptBase64(string $idEmail): ?string;

    public function getLaudoPdf(string $idEmail): string;
}

