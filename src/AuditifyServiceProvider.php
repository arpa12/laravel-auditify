<?php

namespace Auditify;

use Auditify\Console\InstallCommand;
use Auditify\Console\PruneCommand;
use Auditify\Services\AuditifyService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Auditify\Facades\Auditify;
use Auditify\Http\Middleware\TrackPageVisits;
use Auditify\Http\Middleware\BlockXssAttacks;
use Illuminate\Routing\Router;

class AuditifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('auditify', function () {
            return new AuditifyService();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/auditify.php',
            'auditify'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__.'/../database/migrations'
        );

        $this->loadRoutesFrom(
            __DIR__.'/../routes/web.php'
        );

        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'auditify'
        );

        // Register Auth event listeners
        if (config('auditify.track_auth_events', true)) {
            Event::listen(Login::class, function (Login $event) {
                Auditify::logActivity(
                    'Login: ' . ($event->user->email ?? $event->user->name ?? $event->user->id),
                    request()->fullUrl(),
                    $event->user
                );
            });

            Event::listen(Logout::class, function (Logout $event) {
                if ($event->user) {
                    Auditify::logActivity(
                        'Logout: ' . ($event->user->email ?? $event->user->name ?? $event->user->id),
                        request()->fullUrl(),
                        $event->user
                    );
                }
            });

            Event::listen(Failed::class, function (Failed $event) {
                Auditify::logActivity(
                    'Failed Login: ' . ($event->credentials['email'] ?? 'unknown'),
                    request()->fullUrl(),
                    $event->user ?? null
                );
            });
        }

        // Register TrackPageVisits & BlockXssAttacks middlewares to the 'web' group
        if ($this->app->bound(\Illuminate\Contracts\Http\Kernel::class)) {
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->appendMiddlewareToGroup('web', TrackPageVisits::class);
            $this->app->make(\Illuminate\Contracts\Http\Kernel::class)->appendMiddlewareToGroup('web', BlockXssAttacks::class);
        } else {
            $this->app->make(Router::class)->pushMiddlewareToGroup('web', TrackPageVisits::class);
            $this->app->make(Router::class)->pushMiddlewareToGroup('web', BlockXssAttacks::class);
        }

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/auditify.php' =>
                config_path('auditify.php'),
            ], 'auditify-config');

            $this->publishes([
                __DIR__.'/../database/migrations' =>
                database_path('migrations'),
            ], 'auditify-migrations');

            $this->commands([
                InstallCommand::class,
                PruneCommand::class,
            ]);
        }
    }
}
