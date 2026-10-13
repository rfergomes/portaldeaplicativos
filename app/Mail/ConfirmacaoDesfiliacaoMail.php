<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Desfiliacao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoDesfiliacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Desfiliacao $desfiliacao,
        public string $linkConfirmacao
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmação de Solicitação de Desfiliação Sindical - Portal de Aplicativos',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.desfiliacao_link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
