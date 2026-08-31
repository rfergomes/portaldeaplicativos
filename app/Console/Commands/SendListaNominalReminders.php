<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\ListaNominalReminder;
use App\Models\ConvencaoColetiva;
use App\Models\SocioFolha;
use App\Models\SocioFolhaEmailHistorico;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendListaNominalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convencoes:enviar-lembretes-lista-nominal 
                            {--dry-run : Apenas exibe o que seria enviado no console, sem disparar e-mails} 
                            {--test-email= : Envia todos os e-mails disparados para este endereço de teste ao invés dos clientes reais}
                            {--date= : Simula a data atual para fins de testes (formato YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia e-mails automáticos lembrando do envio da lista nominal 15 dias após o vencimento da contribuição (Cláusula 76).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $simulatedDate = $this->option('date');
        $today = $simulatedDate ? Carbon::parse($simulatedDate)->startOfDay() : now()->startOfDay();

        $this->info("Executando rotina de lembretes da Lista Nominal (Cláusula 76).");
        $this->info("Data de referência atual: " . $today->format('d/m/Y'));

        // A cobrança ocorre 15 dias após o vencimento
        $targetVencimento = $today->copy()->subDays(15)->format('Y-m-d');
        $this->info("Data de vencimento procurada (15 dias atrás): {$targetVencimento}");

        // Busca lançamentos com vencimento há exatamente 15 dias, de empresas ativas
        $lancamentos = SocioFolha::whereHas('empresa', function ($query) {
            $query->where('ativo', true);
        })
        ->with(['empresa.clientes' => function ($query) {
            $query->where('ativo', true)
                  ->where('email_valido', true)
                  ->whereNotNull('email')
                  ->where('email', '!=', '');
        }])
        ->where('data_vencimento', $targetVencimento)
        ->whereNull('data_lista') // Se já entregou a lista, não precisa cobrar
        ->get();

        if ($lancamentos->isEmpty()) {
            $this->info("Nenhum lançamento de contribuição pendente de lista nominal encontrado com vencimento em {$targetVencimento}.");
            return 0;
        }

        $this->info("Encontrado(s) " . $lancamentos->count() . " lançamento(s) elegível(is).");

        $dryRun = (bool) $this->option('dry-run');
        $testEmail = $this->option('test-email');

        if ($dryRun) {
            $this->comment("[MODO SIMULAÇÃO / DRY-RUN ATIVO] Nenhum e-mail real será enviado.");
        }

        // Cache das convenções ativas por categoria
        $convencoesPorCategoria = [
            'QUIMICA' => ConvencaoColetiva::ativa()->porCategoria('QUIMICA')->with('clausulas')->latest('vigencia_inicio')->first(),
            'FARMACEUTICA' => ConvencaoColetiva::ativa()->porCategoria('FARMACEUTICA')->with('clausulas')->latest('vigencia_inicio')->first(),
        ];

        $enviadosCount = 0;
        $duplicadosCount = 0;
        $falhasCount = 0;

        foreach ($lancamentos as $lancamento) {
            $empresa = $lancamento->empresa;
            $empresaName = $empresa->razao_social ?? 'Empresa Sem Razão Social';
            $categoriaEmpresa = strtoupper(trim((string) ($empresa->categoria ?? 'QUIMICA')));
            
            // Seleciona a convenção correspondente à categoria da empresa
            $convencao = $convencoesPorCategoria[$categoriaEmpresa] ?? $convencoesPorCategoria['QUIMICA'] ?? null;
            $clausula = null;

            if ($convencao) {
                $clausula = $convencao->clausulas->where('dispara_lembrete_lista_nominal', true)->first()
                    ?? $convencao->clausulas->where('numero', '76')->first()
                    ?? $convencao->clausulas->first();
            }

            $clientes = $empresa->clientes ?? collect();

            if ($clientes->isEmpty()) {
                $this->warn("Aviso: Lançamento ID {$lancamento->id} ({$empresaName}) não possui contatos ativos com e-mail cadastrado.");
                continue;
            }

            $vencimentoFormatado = Carbon::parse($lancamento->data_vencimento)->format('d/m/Y');
            $this->info("\nEmpresa: {$empresaName} (CNPJ: {$empresa->cnpj}) - Categoria: {$categoriaEmpresa} - Vencimento: {$vencimentoFormatado}");
            if ($clausula) {
                $this->info(" - Cláusula Aplicada: Nº {$clausula->numero} ({$clausula->titulo}) da convenção '{$convencao->titulo}'");
            }

            foreach ($clientes as $cliente) {
                $originalEmail = trim((string) $cliente->email);

                if (empty($originalEmail)) {
                    continue;
                }

                // Prevenção de duplicidade: verifica se já enviou para este lançamento e cliente
                $jaEnviado = SocioFolhaEmailHistorico::where('socio_folha_id', $lancamento->id)
                    ->where('cliente_id', $cliente->id)
                    ->where('tipo_envio', 'lista_nominal_15_dias')
                    ->exists();

                if ($jaEnviado) {
                    $this->comment("  [Ignorado] Já foi enviado anteriormente para: {$cliente->nome} <{$originalEmail}>");
                    $duplicadosCount++;
                    continue;
                }

                $numClausula = $clausula ? $clausula->numero : '76';
                $subjectText = "Lembrete: Envio da Relação Nominal de Contribuições - Cláusula {$numClausula}";
                $historicoId = null;

                if (!$dryRun) {
                    $historico = SocioFolhaEmailHistorico::create([
                        'socio_folha_id'     => $lancamento->id,
                        'cliente_id'          => $cliente->id,
                        'email_destinatario' => $originalEmail,
                        'assunto'            => $subjectText,
                        'tipo_envio'         => 'lista_nominal_15_dias',
                        'status'             => 'ENVIADO',
                    ]);
                    $historicoId = $historico->id;
                }

                $mailable = new ListaNominalReminder($lancamento, $cliente, $clausula, $convencao, $historicoId);

                if ($dryRun) {
                    $this->info("  [Simulação] Enviaria e-mail para: {$cliente->nome} <{$originalEmail}>");
                    $this->info("  - Assunto: " . $mailable->envelope()->subject);
                    $enviadosCount++;
                } else {
                    $targetEmail = $testEmail ?: $originalEmail;

                    if ($testEmail) {
                        $mailable->subject("[TESTE para: {$originalEmail}] " . $mailable->envelope()->subject);
                        $this->info("  Enviando e-mail de teste para: <{$targetEmail}> (Destinatário original: {$cliente->nome} <{$originalEmail}>)");
                    } else {
                        $this->info("  Enviando e-mail real para: {$cliente->nome} <{$targetEmail}>");
                    }

                    try {
                        Mail::to($targetEmail)->send($mailable);
                        $enviadosCount++;
                    } catch (\Exception $e) {
                        $this->error("  Falha ao enviar e-mail para {$targetEmail}: " . $e->getMessage());
                        Log::error("Erro no envio de lembrete de lista nominal para {$targetEmail}: " . $e->getMessage());
                        $falhasCount++;

                        if ($historicoId) {
                            SocioFolhaEmailHistorico::where('id', $historicoId)->update([
                                'status' => 'BOUNCE',
                                'bounce_code' => '5.0.0',
                                'bounce_description' => 'Erro local no servidor SMTP: ' . $e->getMessage()
                            ]);
                        }
                    }
                }
            }
        }

        $this->info("\n--- RESUMO DO PROCESSAMENTO ---");
        $this->info("E-mails processados/enviados: {$enviadosCount}");
        $this->info("Envios ignorados por duplicidade: {$duplicadosCount}");
        if ($falhasCount > 0) {
            $this->error("Falhas no disparo: {$falhasCount}");
        }
        $this->info("Processamento concluído com sucesso.");

        return 0;
    }
}
