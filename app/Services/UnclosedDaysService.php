<?php

namespace App\Services;

use App\Models\DailySummary;
use App\Models\TaskInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UnclosedDaysService
{
    public function forUser(int $userId): Collection
    {
        $today = Carbon::today()->toDateString();

        return TaskInstance::query()
            ->where('user_id', $userId)
            ->whereDate('scheduled_date', '<=', $today)
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('daily_summaries')
                    ->whereColumn('daily_summaries.date', 'task_instances.scheduled_date')
                    ->where('daily_summaries.user_id', $userId);
            })
            ->select('scheduled_date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count")
            ->selectRaw("SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as skipped_count")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->groupBy('scheduled_date')
            ->orderBy('scheduled_date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->scheduled_date)->toDateString(),
                'label' => Carbon::parse($row->scheduled_date)->isToday()
                    ? 'Hôm nay'
                    : Carbon::parse($row->scheduled_date)->format('d/m/Y'),
                'total' => (int) $row->total,
                'done' => (int) $row->done_count,
                'skipped' => (int) $row->skipped_count,
                'pending' => (int) $row->pending_count,
                'is_today' => Carbon::parse($row->scheduled_date)->isToday(),
            ]);
    }

    public function countForUser(int $userId): int
    {
        return $this->forUser($userId)->count();
    }

    public function isClosed(int $userId, string $date): bool
    {
        return DailySummary::where('user_id', $userId)
            ->whereDate('date', $date)
            ->exists();
    }
}
