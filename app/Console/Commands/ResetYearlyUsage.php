<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class ResetYearlyUsage extends Command
{
    protected $signature = 'usage:reset-yearly';

    protected $description = 'Reset yearly AI usage for all projects';

    public function handle(): int
    {
        $this->info('Resetting yearly usage for all projects...');

        $count = Project::query()->update(['yearly_usage_twd' => 0]);

        $this->info("Successfully reset usage for {$count} projects.");

        return Command::SUCCESS;
    }
}
