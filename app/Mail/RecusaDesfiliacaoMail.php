<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Desfiliacao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecusaDesfiliacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Desfiliacao $desfiliacao,
        public string $motivoRecusa
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comunicado Oficial: Solicitação de Desfiliação Não Homologada - Sindicato Químicos Unificados',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.desfiliacao.recusada',
        );
    }
}
