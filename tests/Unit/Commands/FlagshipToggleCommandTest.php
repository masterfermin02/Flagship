<?php

namespace Tests\Unit\Commands;

use Flagship\Commands\FlagshipToggleCommand;
use Flagship\Facades\Flagship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlagshipToggleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_command_toggles_flag_status()
    {
        $flagName = 'test_feature';
        Flagship::shouldReceive('toggle')->once()->with($flagName);

        $this->artisan(FlagshipToggleCommand::class, ['name' => $flagName])
            ->expectsOutput("Feature flag '{$flagName}' toggled")
            ->assertExitCode(0);
    }
}
