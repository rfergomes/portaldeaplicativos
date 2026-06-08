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

        // Tenta ler a versão na ordem: Config (Config + ENV) > Arquivo .version > Git
        $appVersion = config('app.version', 'v1.1.0');
        $versionFile = base_path('.version');

        if (file_exists($versionFile)) {
            $appVersion = trim(file_get_contents($versionFile));
        } elseif ($appVersion === 'v1.1.0' || empty($appVersion)) {
            // Se ainda for o padrão ou estiver vazio, tenta Git
            try {
                $hash = trim(@shell_exec('git log -1 --format=%h'));
                $date = trim(@shell_exec('git log -1 --format=%cd --date=format:"%d/%m %H:%M"'));
                if ($hash) {
                    $appVersion = "rev.{$hash} ({$date})";
                }
            } catch (\Exception $e) {
                // Silencioso
            }
        }

        view()->share('appVersion', $appVersion);

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

                $view->with([
                    'demandasAlertasCount' => count($alertas),
                    'demandasAlertasList' => $alertas
                ]);
            } else {
                $view->with([
                    'demandasAlertasCount' => 0,
                    'demandasAlertasList' => []
                ]);
            }
        });
    }
}
