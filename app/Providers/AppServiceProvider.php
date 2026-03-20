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

        // Compartilha a versão do Git com todas as views
        $appVersion = 'v1.0.0';
        try {
            $hash = trim(@exec('git log -1 --format=%h'));
            $date = trim(@exec('git log -1 --format=%cd --date=format:"%d/%m %H:%i"'));
            if ($hash) {
                $appVersion = "rev.{$hash} ({$date})";
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        view()->share('appVersion', $appVersion);
    }
}
