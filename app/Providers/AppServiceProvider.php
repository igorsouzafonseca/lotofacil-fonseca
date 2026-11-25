<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            config([
                'app.locale'   => setting('locale', 'pt_BR'),
                'app.timezone' => setting('timezone', 'America/Sao_Paulo'),
            ]);
            date_default_timezone_set(setting('timezone', 'America/Sao_Paulo'));
            app()->setLocale(setting('locale', 'pt_BR'));
        }

        Gate::define('admin', function ($user) {
            return $user->is_admin; // Ajuste para o nome da coluna no seu banco (ex: is_admin, nivel, etc.)
        });
    }
}
