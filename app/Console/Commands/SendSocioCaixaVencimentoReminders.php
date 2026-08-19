<?php

namespace App\Console\Commands;

use App\Jobs\SendKwikNotificationJob;
use App\Models\SocioCaixa;
use App\Models\SocioCaixaOcorrencia;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSocioCaixaVencimentoReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socios-caixa:enviar-lembretes-vencimento
                            {--dry-run : Apenas exibe as mensagens que seriam enviadas, sem disparar na API Kwik}
                            {--date= : Simula a data de referência (formato YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia mensagens de WhatsApp (API Kwik) lembrando associados ativos sobre mensalidades a vencer em 3 dias.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $simulatedDate = $this->option('date');
        $today = $simulatedDate ? Carbon::parse($simulatedDate)->startOfDay() : now()->startOfDay();
        $targetDate = $today->copy()->addDays(3)->format('Y-m-d');
        $targetFormatted = Carbon::parse($targetDate)->format('d/m/Y');

        $this->info("=== Disparador de Lembretes de Vencimento - Sócio Caixa ===");
        $this->info("Data de Referência: " . $today->format('d/m/Y'));
        $this->info("Data-Alvo de Vencimento (+3 dias): {$targetFormatted} ({$targetDate})");

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->comment("[MODO SIMULAÇÃO / DRY-RUN ATIVO] Nenhuma mensagem real será despachada.");
        }

        // Buscar mensalidades em aberto com vencimento na data alvo, de sócios ativos com telefone
        $lancamentos = SocioCaixa::where('inativado_abaco', false)
            ->where('pago', false)
            ->where('data_vencimento', $targetDate)
            ->whereNotNull('telefone')
            ->where('telefone', '!=', '')
            ->orderBy('matricula')
            ->orderBy('data_vencimento')
            ->get();

        if ($lancamentos->isEmpty()) {
            $this->info("Nenhum lançamento a vencer em {$targetFormatted} encontrado.");
            return 0;
        }

        // Agrupar por matrícula e selecionar apenas 1 lançamento por associado (o mais próximo)
        $porSocio = $lancamentos->groupBy('matricula')->map(function ($grupo) {
            return $grupo->first();
        });

        $this->info("Encontrado(s) " . $porSocio->count() . " associado(s) elegível(is) para notificação.\n");

        $enviadosCount = 0;
        $ignoradosCount = 0;

        foreach ($porSocio as $matricula => $socio) {
            $telefoneLimpo = preg_replace('/\D/', '', $socio->telefone);
            if (empty($telefoneLimpo) || strlen($telefoneLimpo) < 10) {
                $this->warn("Aviso: Sócio {$socio->nome} (Matrícula: {$matricula}) possui telefone inválido: '{$socio->telefone}'. Ignorado.");
                $ignoradosCount++;
                continue;
            }

            // Extrair o primeiro nome
            $primeiroNome = trim(explode(' ', trim($socio->nome))[0]);

            // Formatar a data de vencimento
            $vencimentoFormatado = Carbon::parse($socio->data_vencimento)->format('d/m/Y');

            // Prevenção de duplicidade: Verificar se já foi enviado lembrete para este vencimento nesta matrícula
            $jaEnviado = SocioCaixaOcorrencia::where('matricula', $matricula)
                ->where('mensagem', 'like', "%[WHATSAPP AUTOMÁTICO] Lembrete de vencimento%{$vencimentoFormatado}%")
                ->exists();

            if ($jaEnviado) {
                $this->warn("Aviso: Lembrete para {$socio->nome} ({$matricula}) com vencimento em {$vencimentoFormatado} já foi enviado anteriormente. Ignorado.");
                $ignoradosCount++;
                continue;
            }

            $template = 'lembrete_mensalidade';
            $bodyArgs = [$primeiroNome, $vencimentoFormatado];

            if ($dryRun) {
                $this->info("[SIMULAÇÃO] Enviaria WhatsApp para {$socio->nome} ({$telefoneLimpo})");
                $this->line("  - Template: {$template}");
                $this->line("  - Variáveis: {{1}} = '{$primeiroNome}', {{2}} = '{$vencimentoFormatado}'");
                $enviadosCount++;
            } else {
                // Despachar Job para API Kwik
                SendKwikNotificationJob::dispatch(
                    $socio->telefone,
                    $template,
                    $bodyArgs,
                    null
                );

                // Gravar ocorrência de auditoria
                SocioCaixaOcorrencia::create([
                    'matricula' => $socio->matricula,
                    'user_id'   => null,
                    'mensagem'  => "[WHATSAPP AUTOMÁTICO] Lembrete de vencimento enviado para {$socio->telefone}. Vencimento: {$vencimentoFormatado}",
                ]);

                $this->info("✓ Notificação enfileirada para: {$socio->nome} ({$socio->telefone}) - Vencimento: {$vencimentoFormatado}");
                $enviadosCount++;
            }
        }

        $this->info("\nProcessamento concluído: {$enviadosCount} notificação(ões) processada(s), {$ignoradosCount} ignorada(s).");
        return 0;
    }
}
