<?php

namespace App\Console\Commands;

use App\Mail\MensalidadeVencimentoReminder;
use App\Models\SocioFolha;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMensalidadeReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socios-folha:enviar-lembretes 
                            {--dry-run : Apenas exibe o que seria enviado no console, sem disparar e-mails} 
                            {--test-email= : Envia todos os e-mails disparados para este endereço de teste ao invés dos clientes reais}
                            {--date= : Simula a data atual para fins de testes (formato YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia e-mails de lembrete de vencimento da Mensalidade Associativa faltando 10, 5 e 1 dia para o vencimento.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define a data "atual" (real ou simulada)
        $simulatedDate = $this->option('date');
        $today = $simulatedDate ? Carbon::parse($simulatedDate)->startOfDay() : now()->startOfDay();

        $this->info("Executando disparador de lembretes. Data de referência: " . $today->format('d/m/Y'));

        // Datas de vencimento procuradas
        $date10 = $today->copy()->addDays(10)->format('Y-m-d');
        $date5 = $today->copy()->addDays(5)->format('Y-m-d');
        $date1 = $today->copy()->addDays(1)->format('Y-m-d');

        $this->info("Filtros de data_vencimento:");
        $this->info(" - Faltando 10 dias: {$date10}");
        $this->info(" - Faltando 5 dias: {$date5}");
        $this->info(" - Faltando 1 dia: {$date1}");

        // Busca registros da folha a vencer nesses dias específicos e que não estejam pagos
        $reminders = SocioFolha::with(['empresa.clientes' => function ($query) {
            $query->where('ativo', true)
                  ->whereNotNull('email')
                  ->where('email', '!=', '');
        }])
        ->where('situacao', '!=', 'PAGO')
        ->whereIn('data_vencimento', [$date10, $date5, $date1])
        ->get();

        if ($reminders->isEmpty()) {
            $this->info("Nenhum lançamento de Sócio Folha a vencer encontrado para estas datas.");
            return 0;
        }

        $this->info("Encontrado(s) " . $reminders->count() . " lançamento(s) pendente(s).");

        $dryRun = $this->option('dry-run');
        $testEmail = $this->option('test-email');

        if ($dryRun) {
            $this->comment("[MODO SIMULAÇÃO / DRY-RUN ATIVO] Nenhum e-mail real será enviado.");
        }

        foreach ($reminders as $reminder) {
            $vencimento = Carbon::parse($reminder->data_vencimento)->startOfDay();
            $daysRemaining = $today->diffInDays($vencimento);

            $empresaName = $reminder->empresa->razao_social ?? 'Empresa Sem Nome';
            $clientes = $reminder->empresa->clientes ?? collect();

            if ($clientes->isEmpty()) {
                $this->warn("Aviso: Lançamento ID {$reminder->id} ({$empresaName}) não possui contatos ativos com e-mail cadastrado.");
                continue;
            }

            $this->info("\nEmpresa: {$empresaName} (Vence em {$vencimento->format('d/m/Y')} - Faltam {$daysRemaining} dias)");

            foreach ($clientes as $cliente) {
                $originalEmail = trim($cliente->email);
                
                if (empty($originalEmail)) {
                    continue;
                }

                $tipoEnvio = $daysRemaining . '_dias';
                if ($daysRemaining === 1) {
                    $tipoEnvio = '1_dia';
                }

                $historicoId = null;

                if (!$dryRun) {
                    $subjectText = 'Lembrete de Vencimento - Mensalidade Associativa';
                    if ($daysRemaining === 10) {
                        $subjectText = 'Lembrete: Vencimento de Mensalidade Associativa em 10 dias';
                    } elseif ($daysRemaining === 5) {
                        $subjectText = 'Atenção: Vencimento de Mensalidade Associativa em 5 dias';
                    } elseif ($daysRemaining === 1) {
                        $subjectText = 'URGENTE: A Mensalidade Associativa da sua empresa vence amanhã!';
                    }

                    $historico = \App\Models\SocioFolhaEmailHistorico::create([
                        'socio_folha_id'     => $reminder->id,
                        'cliente_id'          => $cliente->id,
                        'email_destinatario' => $originalEmail,
                        'assunto'            => $subjectText,
                        'tipo_envio'         => $tipoEnvio,
                        'status'             => 'ENVIADO',
                    ]);
                    $historicoId = $historico->id;
                }

                $mailable = new MensalidadeVencimentoReminder($reminder, $cliente, $daysRemaining, $historicoId);

                if ($dryRun) {
                    $this->info("  [Simulação] Enviaria e-mail para: {$cliente->nome} <{$originalEmail}>");
                    $this->info("  - Assunto: " . $mailable->envelope()->subject);
                } else {
                    $targetEmail = $testEmail ?: $originalEmail;
                    
                    if ($testEmail) {
                        // Personaliza o assunto no teste para mostrar o destinatário original
                        $mailable->subject("[TESTE para: {$originalEmail}] " . $mailable->envelope()->subject);
                        $this->info("  Enviando e-mail de teste para: <{$targetEmail}> (Destinatário original: {$cliente->nome} <{$originalEmail}>)");
                    } else {
                        $this->info("  Enviando e-mail real para: {$cliente->nome} <{$targetEmail}>");
                    }

                    try {
                        Mail::to($targetEmail)->send($mailable);
                    } catch (\Exception $e) {
                        $this->error("  Falha ao enviar e-mail para {$targetEmail}: " . $e->getMessage());
                        // Update status to failed or log it
                        if ($historicoId) {
                            \App\Models\SocioFolhaEmailHistorico::where('id', $historicoId)->update([
                                'status' => 'BOUNCE',
                                'bounce_code' => '5.0.0',
                                'bounce_description' => 'Erro local no servidor SMTP: ' . $e->getMessage()
                            ]);
                        }
                    }
                }
            }
        }

        $this->info("\nProcessamento de e-mails concluído.");
        return 0;
    }
}
