<?php

namespace App\Console\Commands;

use App\Actions\GenerateDailyTaskInstancesAction;
use Illuminate\Console\Command;

class GenerateDailyTaskInstancesCommand extends Command
{
    protected $signature = 'linhtinh:generate-tasks {--date= : Date to generate tasks for (Y-m-d)}';

    protected $description = 'Generate daily task instances from active templates';

    public function handle(GenerateDailyTaskInstancesAction $action): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : \Carbon\Carbon::today();

        $created = $action->execute($date);

        $this->info("Created {$created} task instances for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
