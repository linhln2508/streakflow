<?php

namespace App\Console\Commands;

use App\Actions\GenerateDailyTaskInstancesAction;
use Illuminate\Console\Command;

class GenerateDailyTaskInstancesCommand extends Command
{
    protected $signature = 'linhtinh:generate-tasks
                            {--date= : Date to generate tasks for (Y-m-d). When omitted, backfills missing days through today}
                            {--no-backfill : Only generate for the given date (or today), skip gap fill}';

    protected $description = 'Generate daily task instances from active templates, filling any missing prior days';

    public function handle(GenerateDailyTaskInstancesAction $action): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : \Carbon\Carbon::today();

        if ($this->option('no-backfill') || $this->option('date')) {
            $created = $action->execute($date);
            $this->info("Created {$created} task instances for {$date->toDateString()}.");
        } else {
            $created = $action->executeWithBackfill($date);
            $this->info("Created {$created} task instances through {$date->toDateString()} (including any missing prior days).");
        }

        return self::SUCCESS;
    }
}
