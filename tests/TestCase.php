<?php

namespace Tests;

use Flagship\FlagshipServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // config()->set('database.default', 'testbench');
        // config()->set('database.connections.testbench', [
        //     'driver'   => 'sqlite',
        //     'database' => ':memory:',
        //     'prefix'   => '',
        // ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->artisan('migrate', ['--database' => 'testbench'])->run();


        // Manually register factories
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $namespace = 'Database\\Factories\\';

            // For package specific models:
            if (str_starts_with($modelName, 'Flagship\\Models\\')) {
                $modelName = class_basename($modelName);
                return 'Flagship\\Factories\\' . $modelName . 'Factory';
            }

            // Default Laravel app models (if any for testing)
            $modelName = str_replace('App\\Models\\', '', $modelName);
            return $namespace . $modelName . 'Factory';
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            FlagshipServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Flagship' => \Flagship\Facades\Flagship::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup other necessary configurations for your package
        $app['config']->set('flagship.cache_enabled', false); // Disable cache for tests by default
        $app['config']->set('flagship.default_state', false);
    }
}
