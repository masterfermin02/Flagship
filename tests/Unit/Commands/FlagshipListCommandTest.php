<?php

namespace Tests\Unit\Commands;

use Flagship\Commands\FlagshipListCommand;
use Flagship\Facades\Flagship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlagshipListCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_command_displays_no_flags_message()
    {
        Flagship::shouldReceive('all')->once()->andReturn([]);

        $this->artisan(FlagshipListCommand::class)
            ->expectsOutput('There are no registered feature flags')
            ->assertExitCode(0);
    }

    public function test_list_command_displays_flags_table()
    {
        $flags = [
            ['name' => 'feature_one', 'is_active' => true, 'description' => 'Description one', 'updated_at' => now()->toDateTimeString()],
            ['name' => 'feature_two', 'is_active' => false, 'description' => 'Description two', 'updated_at' => now()->toDateTimeString()],
        ];

        Flagship::shouldReceive('all')->once()->andReturn($flags);

        $this->artisan(FlagshipListCommand::class)
            ->expectsTable(
                ['Name', 'Status', 'Description', 'Updated'],
                [
                    ['feature_one', '✅ Active', 'Description one', $flags[0]['updated_at']],
                    ['feature_two', '❌ Inactive', 'Description two', $flags[1]['updated_at']],
                ]
            )
            ->assertExitCode(0);
    }
}
