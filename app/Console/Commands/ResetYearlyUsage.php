<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;

class ResetYearlyUsage extends Command
{
    protected $signature = 'usage:reset-yearly';

    protected $description = 'Reset yearly AI usage';

    public function handle(): int
    {
        SiteSetting::query()->update(['yearly_usage_twd' => 0]);

        $this->info('Successfully reset yearly AI usage.');

        return Command::SUCCESS;
    }
}
