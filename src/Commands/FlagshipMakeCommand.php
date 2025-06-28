<?php

namespace Flagship\Commands;

use Flagship\Facades\Flagship;
use Illuminate\Console\Command;

class FlagshipMakeCommand extends Command
{
    protected $signature = 'flagship:make {name} {--enabled} {--description=}';
    protected $description = 'Create a new feature flag';

    public function handle()
    {
        $name = $this->argument('name');
        $enabled = $this->option('enabled') ?: config('flagship.default_state', false);
        $description = $this->option('description') ?: "Feature flag: {$name}";

        Flagship::create($name, $enabled);

        $status = $enabled ? 'enabled' : 'disabled';
        $this->info("Feature flag '{$name}' created {$status}");
    }
}
