<?php

namespace Flagship\Commands;

use Flagship\Facades\Flagship;
use Illuminate\Console\Command;

class FlagshipListCommand extends Command
{
    protected $signature = 'flagship:list';
    protected $description = 'List all feature flags';

    public function handle()
    {
        $flags = Flagship::all();

        if (empty($flags)) {
            $this->info('There are no registered feature flags');
            return;
        }

        $headers = ['Name', 'Status', 'Description', 'Updated'];
        $rows = [];

        foreach ($flags as $flag) {
            $rows[] = [
                $flag['name'],
                $flag['is_active'] ? '✅ Active' : '❌ Inactive',
                $flag['description'] ?? '',
                $flag['updated_at'] ?? ''
            ];
        }

        $this->table($headers, $rows);
    }
}
