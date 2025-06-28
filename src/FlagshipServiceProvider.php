<?php

namespace Flagship;

use Flagship\Blade\FlagshipDirective;
use Flagship\Commands\FlagshipMakeCommand;
use Flagship\Commands\FlagshipListCommand;
use Flagship\Commands\FlagshipToggleCommand;
use Flagship\Contracts\FlagshipInterface;
use Flagship\Services\FlagshipService;
use Illuminate\Support\ServiceProvider;

class FlagshipServiceProvider extends ServiceProvider
{
    /**
     * Register the package's services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/flagship.php',
            'flagship'
        );

        $this->app->singleton(FlagshipInterface::class, FlagshipService::class);
        $this->app->alias(FlagshipInterface::class, 'flagship');

        FlagshipDirective::register();
    }

    /**
     * Bootstrap the package's services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/flagship.php' => config_path('flagship.php'),
        ], 'flagship-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'flagship-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->callAfterResolving('blade.compiler', function ($blade) {
            $blade->if('flagship', function ($flag, $user = null) {
                 return resolve(FlagshipInterface::class)->isEnabled($flag, $user);
            });
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                FlagshipMakeCommand::class,
                FlagshipListCommand::class,
                FlagshipToggleCommand::class,
            ]);
        }
    }
}
