<?php

namespace Flagship\Commands;

use Flagship\Facades\Flagship;
use Illuminate\Console\Command;

class FlagshipToggleCommand extends Command
{
    protected $signature = 'flagship:toggle {name}';
    protected $description = 'Toggle the state of a feature flag';

    public function handle()
    {
        $name = $this->argument('name');
        
        Flagship::toggle($name);
        
        $this->info("Feature flag '{$name}' toggled");
    }
}
