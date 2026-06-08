<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocioFolhaEmailHistorico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmtpWebhookController extends Controller
{
    /**
     * Recebe notificações de falhas de entrega (Bounces).
     */
    public function handleBounce(Request $request)
    {
        $bounceDescription = $request->input('bounce_description');
        $bounceCode = $request->input('bounce_code');
        $sender = $request->input('sender');
        $to = $request->input('to');
        $subject = $request->input('subject');
        $xSmtplw = $request->input('x-smtplw'); // Nosso historicoId

        // Grava no arquivo public/myfile.txt como solicitado na especificação
        $this->writeToTxtLog("bounce_description: $bounceDescription\tbounce_code: $bounceCode\tsender: $sender\tto: $to\tsubject: $subject\tx_smtplw: $xSmtplw");

        // Atualiza a tabela de histórico no banco de dados se o x-smtplw for um ID válido
        $historico = null;
        if (!empty($xSmtplw) && is_numeric($xSmtplw)) {
            $historico = SocioFolhaEmailHistorico::find($xSmtplw);
            if ($historico) {
                $historico->update([
                    'status' => 'BOUNCE',
                    'bounce_code' => $bounceCode,
                    'bounce_description' => $bounceDescription,
                ]);
                Log::info("SMTP Webhook: Histórico de e-mail ID $xSmtplw atualizado para BOUNCE (Código: $bounceCode)");
            } else {
                Log::warning("SMTP Webhook: Histórico de e-mail ID $xSmtplw não foi encontrado no banco.");
            }
        } else {
            Log::warning("SMTP Webhook: Recebido bounce com x-smtplw inválido ou ausente: '$xSmtplw'");
        }

        // Desativa o e-mail nos contatos associados ao destinatário
        $emailDestinatario = !empty($to) ? $to : ($historico->email_destinatario ?? null);
        if (!empty($emailDestinatario)) {
            \App\Models\Cliente::where('email', $emailDestinatario)->update([
                'email_valido' => false,
                'email_bounce_code' => $bounceCode,
                'email_bounce_description' => $bounceDescription,
            ]);
            Log::info("SMTP Webhook: Todos os contatos com e-mail '{$emailDestinatario}' foram marcados como bounce/inválidos (Código: $bounceCode).");
        }

        // Retorna status 200 OK com texto 'OK'
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Recebe notificações de aberturas de e-mails.
     */
    public function handleOpening(Request $request)
    {
        $sender = $request->input('sender');
        $to = $request->input('to');
        $subject = $request->input('subject');
        $xSmtplw = $request->input('x-smtplw'); // Nosso historicoId
        $openedAt = $request->input('opened_at'); // Data e horário da abertura

        // Grava no arquivo public/myfile.txt como solicitado na especificação
        $this->writeToTxtLog("sender: $sender\tto: $to\tsubject: $subject\tx_smtplw: $xSmtplw\topened at: $openedAt");

        // Atualiza a tabela de histórico no banco de dados se o x-smtplw for um ID válido
        if (!empty($xSmtplw) && is_numeric($xSmtplw)) {
            $historico = SocioFolhaEmailHistorico::find($xSmtplw);
            if ($historico) {
                // Tenta fazer o parse da data enviada pela Locaweb
                $parsedDate = null;
                try {
                    $parsedDate = !empty($openedAt) ? Carbon::parse($openedAt) : now();
                } catch (\Exception $e) {
                    $parsedDate = now();
                }

                $historico->update([
                    'status' => 'ABERTO',
                    'opened_at' => $parsedDate,
                ]);
                Log::info("SMTP Webhook: Histórico de e-mail ID $xSmtplw atualizado para ABERTO (Abertura: $parsedDate)");
            } else {
                Log::warning("SMTP Webhook: Histórico de e-mail ID $xSmtplw não foi encontrado no banco.");
            }
        } else {
            Log::warning("SMTP Webhook: Recebido abertura com x-smtplw inválido ou ausente: '$xSmtplw'");
        }

        // Retorna status 200 OK com texto 'OK'
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Escreve o texto formatado no arquivo public/myfile.txt
     */
    private function writeToTxtLog(string $logText)
    {
        try {
            $filePath = public_path('myfile.txt');
            $date = date('m/d/Y H:i:s');
            $textToWrite = "[$date] " . $logText . PHP_EOL;
            
            // Abre em modo append, cria se não existir
            file_put_contents($filePath, $textToWrite, FILE_APPEND);
        } catch (\Exception $e) {
            Log::error("SMTP Webhook: Falha ao escrever no arquivo myfile.txt: " . $e->getMessage());
        }
    }
}
