<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Domain\Protocolos\Contracts\ArOnlineClient::class,
            \App\Domain\Protocolos\Services\ArOnlineHttpClient::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Compartilha a versão oficial ou revisão do último commit do Git com as views
        view()->share('appVersion', $this->resolverVersaoApp());

        RateLimiter::for('aronline-api', function (object $job) {
            // 0.5 req/s = 30 req/minute
            return Limit::perMinute(30);
        });

        RateLimiter::for('kwik-api', function (object $job) {
            // Safe limit for Kwik if not specified, using same as AR Online for consistency unless informed otherwise
            return Limit::perMinute(60);
        });

        // View Composer para carregar notificações de demandas no topo da tela (layouts.app)
        view()->composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $userId = auth()->id();

                // 1. Novas demandas não lidas atribuídas ao usuário logado
                $novasDemandas = \App\Models\Demanda::where('status', \App\Models\Demanda::STATUS_ABERTA)
                    ->where('tipo_responsavel', 'usuario')
                    ->where('responsavel_usuario_id', $userId)
                    ->where('lida_pelo_responsavel', false)
                    ->get();

                // 2. Demandas do usuário (criadas ou atribuídas) expirando em menos de 24 horas
                $expirandoDemandas = \App\Models\Demanda::whereIn('status', [\App\Models\Demanda::STATUS_ABERTA, \App\Models\Demanda::STATUS_AGUARDANDO])
                    ->whereNotNull('prazo')
                    ->where('prazo', '>=', now())
                    ->where('prazo', '<=', now()->addHours(24))
                    ->where(function($q) use ($userId) {
                        $q->where('criador_id', $userId)
                          ->orWhere(function($sub) use ($userId) {
                              $sub->where('tipo_responsavel', 'usuario')
                                  ->where('responsavel_usuario_id', $userId);
                          });
                    })
                    ->get();

                // Montar array unificado de notificações/alertas
                $alertas = [];

                foreach ($novasDemandas as $d) {
                    $alertas[] = [
                        'id' => $d->id,
                        'tipo' => 'nova',
                        'icone' => 'fa-solid fa-plus-circle text-primary',
                        'titulo' => 'Nova Demanda Atribuída',
                        'mensagem' => \Illuminate\Support\Str::limit($d->titulo, 30),
                        'url' => route('demandas.show', $d->id),
                    ];
                }

                foreach ($expirandoDemandas as $d) {
                    $alertas[] = [
                        'id' => $d->id,
                        'tipo' => 'expirando',
                        'icone' => 'fa-solid fa-triangle-exclamation text-warning',
                        'titulo' => 'Prazo Próximo do Fim',
                        'mensagem' => \Illuminate\Support\Str::limit($d->titulo, 30),
                        'url' => route('demandas.show', $d->id),
                    ];
                }

                // 3. Notificações de desfiliações: (A) Pendentes de Recepção/Ciência e (B) Pendentes de Baixa no ERP Ábaco
                $desfiliacoesPendentesRecepcaoCount = 0;
                $desfiliacoesPendentesBaixaCount = 0;
                $desfiliacoesTotalPendencias = 0;
                $desfiliacoesPendentesAmostra = collect();

                $user = auth()->user();
                if ($user && ($user->can('desfiliacoes.visualizar') || $user->can('socio_caixa.visualizar') || $user->temPerfil('Administrador'))) {
                    try {
                        // (A) Pendentes de Recepção / Aceite (status: confirmada)
                        $desfiliacoesRecepcao = \App\Models\Desfiliacao::where('status', 'confirmada')
                            ->latest('confirmado_em')
                            ->take(5)
                            ->get();
                        $desfiliacoesPendentesRecepcaoCount = \App\Models\Desfiliacao::where('status', 'confirmada')->count();

                        foreach ($desfiliacoesRecepcao as $desf) {
                            $alertas[] = [
                                'id' => 'desf-rec-' . $desf->id,
                                'tipo' => 'desfiliacao_recepcao',
                                'icone' => 'fa-solid fa-envelope-open-text text-warning',
                                'titulo' => 'Nova Carta (Pendente Recepção)',
                                'mensagem' => \Illuminate\Support\Str::limit($desf->nome_completo . ' (' . ($desf->empresa ?: 'Sócio') . ')', 35),
                                'url' => route('admin.desfiliacoes.index', ['status' => 'confirmada']),
                            ];
                        }

                        // (B) Pendentes de Baixa no ERP Ábaco (status: recepcionada)
                        $desfiliacoesBaixa = \App\Models\Desfiliacao::where('status', 'recepcionada')
                            ->latest('recepcionado_em')
                            ->take(5)
                            ->get();
                        $desfiliacoesPendentesBaixaCount = \App\Models\Desfiliacao::where('status', 'recepcionada')->count();

                        foreach ($desfiliacoesBaixa as $desf) {
                            $alertas[] = [
                                'id' => 'desf-bx-' . $desf->id,
                                'tipo' => 'desfiliacao_baixa',
                                'icone' => 'fa-solid fa-clipboard-check text-primary',
                                'titulo' => 'Pendente Baixa no ERP Ábaco',
                                'mensagem' => \Illuminate\Support\Str::limit($desf->nome_completo . ' (' . ($desf->empresa ?: 'Sócio') . ')', 35),
                                'url' => route('admin.desfiliacoes.index', ['status' => 'recepcionada']),
                            ];
                        }

                        $desfiliacoesTotalPendencias = $desfiliacoesPendentesRecepcaoCount + $desfiliacoesPendentesBaixaCount;

                        if ($desfiliacoesTotalPendencias > 0) {
                            $desfiliacoesPendentesAmostra = \App\Models\Desfiliacao::whereIn('status', ['confirmada', 'recepcionada'])
                                ->latest('confirmado_em')
                                ->take(6)
                                ->get();
                        }
                    } catch (\Throwable $e) {
                        // Silencia caso a tabela ainda não exista em contextos de bootstrap
                    }
                }

                $view->with([
                    'demandasAlertasCount' => count($alertas),
                    'demandasAlertasList' => $alertas,
                    'desfiliacoesPendentesRecepcaoCount' => $desfiliacoesPendentesRecepcaoCount,
                    'desfiliacoesPendentesBaixaCount' => $desfiliacoesPendentesBaixaCount,
                    'desfiliacoesTotalPendencias' => $desfiliacoesTotalPendencias,
                    'desfiliacoesPendentesAmostra' => $desfiliacoesPendentesAmostra,
                ]);
            } else {
                $view->with([
                    'demandasAlertasCount' => 0,
                    'demandasAlertasList' => [],
                    'desfiliacoesPendentesRecepcaoCount' => 0,
                    'desfiliacoesPendentesBaixaCount' => 0,
                    'desfiliacoesTotalPendencias' => 0,
                    'desfiliacoesPendentesAmostra' => collect(),
                ]);
            }
        });
    }

    /**
     * Resolve a versão da aplicação ou revisão do último commit do Git para o footer.
     */
    private function resolverVersaoApp(): string
    {
        $versionFile = base_path('.version');
        if (file_exists($versionFile)) {
            $conteudo = trim((string) file_get_contents($versionFile));
            if (!empty($conteudo)) {
                return $conteudo;
            }
        }

        // 1. Tenta obter diretamente via comando do Git
        try {
            $gitOutput = trim((string) @shell_exec('git log -1 --format="rev.%h (%cd)" --date=format:"%d/%m %H:%M"'));
            if (!empty($gitOutput) && str_starts_with($gitOutput, 'rev.')) {
                return $gitOutput;
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        // 2. Fallback: Leitura direta dos arquivos do repositório .git (funciona mesmo com shell_exec bloqueado no PHP)
        try {
            $headFile = base_path('.git/HEAD');
            if (file_exists($headFile)) {
                $headContent = trim((string) file_get_contents($headFile));
                if (str_starts_with($headContent, 'ref: ')) {
                    $ref = trim(substr($headContent, 5));
                    $refPath = base_path('.git/' . $ref);
                    if (file_exists($refPath)) {
                        $hash = substr(trim((string) file_get_contents($refPath)), 0, 7);
                        $data = date('d/m H:i', filemtime($refPath));
                        return "rev.{$hash} ({$data})";
                    }

                    // Tenta em packed-refs
                    $packedRefsPath = base_path('.git/packed-refs');
                    if (file_exists($packedRefsPath)) {
                        $packed = file_get_contents($packedRefsPath);
                        if (preg_match('/^([a-f0-9]{40})\s+' . preg_quote($ref, '/') . '$/m', $packed, $m)) {
                            $hash = substr($m[1], 0, 7);
                            $data = date('d/m H:i', filemtime($packedRefsPath));
                            return "rev.{$hash} ({$data})";
                        }
                    }
                } elseif (preg_match('/^[a-f0-9]{40}$/i', $headContent)) {
                    $hash = substr($headContent, 0, 7);
                    $data = date('d/m H:i', filemtime($headFile));
                    return "rev.{$hash} ({$data})";
                }
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        // 3. Fallback de configuração caso seja uma versão personalizada no .env
        $configVersion = config('app.version');
        if (!empty($configVersion) && !in_array($configVersion, ['1.1.0', 'v1.1.0', '1.0.0', 'v1.0.0'], true)) {
            return $configVersion;
        }

        return 'rev.' . substr(md5_file(base_path('composer.json')) ?: '1.1.0', 0, 7);
    }
}
