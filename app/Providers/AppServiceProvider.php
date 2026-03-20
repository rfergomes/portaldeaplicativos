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

        // Tenta ler a versão de um arquivo (gerado no deploy) ou do Git
        $appVersion = 'v1.0.0';
        $versionFile = base_path('.version');

        if (file_exists($versionFile)) {
            $appVersion = trim(file_get_contents($versionFile));
        } else {
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
