<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Desfiliacao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacaoDiretoriaDesfiliacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Desfiliacao $desfiliacao,
        public string $pdfAbsolutePath
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[NOTIFICAÇÃO] Nova Desfiliação Confirmada - ' . $this->desfiliacao->nome_completo . ' (' . $this->desfiliacao->empresa . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.desfiliacao_notificacao_diretoria',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfAbsolutePath)
                ->as('Termo_Desfiliacao_' . preg_replace('/\D/', '', $this->desfiliacao->cpf) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
