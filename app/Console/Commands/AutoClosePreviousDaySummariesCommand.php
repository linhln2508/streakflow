<?php

namespace App\Console\Commands;

use App\Actions\CloseDaySummaryAction;
use App\Models\DailySummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoClosePreviousDaySummariesCommand extends Command
{
    protected $signature = 'linhtinh:auto-close {--date= : Date to close (Y-m-d), defaults to yesterday}';

    protected $description = 'Auto-close daily summaries for users who did not close the previous day';

    public function handle(CloseDaySummaryAction $action): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $dateStr = $date->toDateString();
        $closedUserIds = DailySummary::where('date', $dateStr)->pluck('user_id');

        $users = User::whereNotIn('id', $closedUserIds)->get();
        $count = 0;

        foreach ($users as $user) {
            $hasTasks = $user->taskInstances()->where('scheduled_date', $dateStr)->exists();

            if ($hasTasks) {
                $action->execute($user->id, $date, 'system');
                $count++;
            }
        }

        $this->info("Auto-closed {$count} days for {$dateStr}.");

        return self::SUCCESS;
    }
}
