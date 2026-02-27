<?php

namespace App\Domain\Protocolos\DTOs;

class ArOnlineSendPayload
{
    public function __construct(
        public string $nameTo,
        public string $subject,
        public string $contentHtml,
        public ?string $emailTo = null,
        public ?array $attachments = null,
        public ?string $customId = null,
        public ?array $sms = null,
        public ?array $whatsapp = null,
        public ?array $voz = null,
        public ?array $carta = null,
        public ?array $validation = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'nameTo' => $this->nameTo,
            'subject' => $this->subject,
            'content' => $this->contentHtml,
            'customID' => $this->customId ?? '',
        ];

        if ($this->emailTo) {
            $payload['to'] = $this->emailTo;
        }

        if ($this->attachments) {
            $payload['attachments'] = $this->attachments;
        }

        if ($this->sms) {
            $payload['sms'] = $this->sms;
        }

        if ($this->whatsapp) {
            $payload['whatsapp'] = $this->whatsapp;
        }

        if ($this->voz) {
            $payload['voz'] = $this->voz;
        }

        if ($this->carta) {
            $payload['carta'] = $this->carta;
        }

        if ($this->validation) {
            $payload['validation'] = $this->validation;
        }

        return $payload;
    }
}

