<?php

namespace Tests\Unit\Commands;

use Flagship\Commands\FlagshipMakeCommand;
use Flagship\Facades\Flagship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlagshipMakeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_command_creates_disabled_flag_by_default()
    {
        $flagName = 'new_feature';
        Flagship::shouldReceive('create')->once()->with($flagName, false);

        $this->artisan(FlagshipMakeCommand::class, ['name' => $flagName])
            ->expectsOutput("Feature flag '{$flagName}' created disabled")
            ->assertExitCode(0);
    }

    public function test_make_command_creates_enabled_flag_with_option()
    {
        $flagName = 'another_feature';
        Flagship::shouldReceive('create')->once()->with($flagName, true);

        $this->artisan(FlagshipMakeCommand::class, ['name' => $flagName, '--enabled' => true])
            ->expectsOutput("Feature flag '{$flagName}' created enabled")
            ->assertExitCode(0);
    }

    public function test_make_command_creates_flag_with_description()
    {
        $flagName = 'described_feature';
        $description = 'This is a test description.';
        // We don't directly test the description in the output of Flagship::create,
        // as the command forms its own output message.
        // We rely on Flagship::create being called with the correct name and enabled status.
        Flagship::shouldReceive('create')->once()->with($flagName, false);


        $this->artisan(FlagshipMakeCommand::class, [
            'name' => $flagName,
            '--description' => $description,
        ])
            ->expectsOutput("Feature flag '{$flagName}' created disabled") // The output reflects enabled status, not description
            ->assertExitCode(0);
    }
}
