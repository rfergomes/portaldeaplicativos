<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Cliente;
use App\Models\ConvencaoClausula;
use App\Models\ConvencaoColetiva;
use App\Models\SocioFolha;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListaNominalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public SocioFolha $socio;
    public Cliente $cliente;
    public ?ConvencaoClausula $clausula;
    public ?ConvencaoColetiva $convencao;
    public ?int $historicoId;

    /**
     * Create a new message instance.
     */
    public function __construct(
        SocioFolha $socio,
        Cliente $cliente,
        ?ConvencaoClausula $clausula = null,
        ?ConvencaoColetiva $convencao = null,
        ?int $historicoId = null
    ) {
        $this->socio = $socio;
        $this->cliente = $cliente;
        $this->clausula = $clausula;
        $this->convencao = $convencao;
        $this->historicoId = $historicoId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $numClausula = $this->clausula ? $this->clausula->numero : '76';
        $subject = "Lembrete: Envio da Relação Nominal de Contribuições - Cláusula {$numClausula}";

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
            view: 'emails.lista_nominal_reminder',
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
