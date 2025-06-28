<?php

namespace Tests;

use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

#[WithMigration]
#[WithEnv('DB_CONNECTION', 'testing')]
abstract class TestCase extends OrchestraTestCase
{
    use WithWorkbench;
}
