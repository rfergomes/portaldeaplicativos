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

class TermoDesfiliacaoTrabalhadorMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $pdfAbsolutePath;

    public function __construct(
        public Desfiliacao $desfiliacao,
        ?string $pdfAbsolutePath = null
    ) {
        $this->pdfAbsolutePath = $pdfAbsolutePath ?: (
            $this->desfiliacao->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($this->desfiliacao->pdf_path)
                ? \Illuminate\Support\Facades\Storage::disk('local')->path($this->desfiliacao->pdf_path)
                : null
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Termo de Desfiliação Sindical Homologado - Cópia Oficial com Trilha Probatória',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.desfiliacao_termo_trabalhador',
        );
    }

    public function attachments(): array
    {
        if ($this->pdfAbsolutePath && file_exists($this->pdfAbsolutePath)) {
            return [
                Attachment::fromPath($this->pdfAbsolutePath)
                    ->as('Termo_Desfiliacao_' . preg_replace('/\D/', '', $this->desfiliacao->cpf) . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
