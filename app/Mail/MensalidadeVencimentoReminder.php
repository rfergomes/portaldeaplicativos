<?php

namespace App\Mail;

use App\Models\Cliente;
use App\Models\SocioFolha;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MensalidadeVencimentoReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $socio;
    public $cliente;
    public $daysRemaining;
    public $historicoId;

    /**
     * Create a new message instance.
     */
    public function __construct(SocioFolha $socio, Cliente $cliente, int $daysRemaining, $historicoId = null)
    {
        $this->socio = $socio;
        $this->cliente = $cliente;
        $this->daysRemaining = $daysRemaining;
        $this->historicoId = $historicoId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Lembrete de Vencimento - Mensalidade Associativa';
        
        if ($this->daysRemaining === 10) {
            $subject = 'Lembrete: Vencimento de Mensalidade Associativa em 10 dias';
        } elseif ($this->daysRemaining === 5) {
            $subject = 'Atenção: Vencimento de Mensalidade Associativa em 5 dias';
        } elseif ($this->daysRemaining === 1) {
            $subject = 'URGENTE: A Mensalidade Associativa da sua empresa vence amanhã!';
        }

        $envelopeData = [
            'subject' => $subject,
        ];

        if ($this->historicoId) {
            $envelopeData['using'] = [
                function ($message) {
                    $message->getHeaders()->addTextHeader('x-smtplw', (string) $this->historicoId);
                }
            ];
        }

        return new Envelope(...$envelopeData);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mensalidade_reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
