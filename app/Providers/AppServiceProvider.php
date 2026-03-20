<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    }
}
