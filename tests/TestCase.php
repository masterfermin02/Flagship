<?php

namespace Tests;

use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ReflectionClass;

#[WithMigration]
#[WithEnv('DB_CONNECTION', 'testing')]
abstract class TestCase extends OrchestraTestCase
{
    use WithWorkbench;

    public function invokeEvaluateRules($service, $rules, $user)
    {
        $ref = new ReflectionClass($service);
        $method = $ref->getMethod('evaluateRules');
        $method->setAccessible(true);
        return $method->invoke($service, $rules, $user);
    }
}
