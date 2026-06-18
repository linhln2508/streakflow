<?php

namespace App\Services;

use App\Models\TaskInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DayViewService
{
    public function __construct(
        protected GamificationService $gamification,
    ) {}

    public function loadInstances(int $userId, Carbon $date): Collection
    {
        return TaskInstance::with(['template.category'])
            ->where('user_id', $userId)
            ->whereDate('scheduled_date', $date)
            ->get()
            ->each(function (TaskInstance $instance) {
                $instance->setAttribute('is_overdue', $instance->isOverdue());
            })
            ->sort(function (TaskInstance $a, TaskInstance $b) {
                $aCompleted = in_array($a->status, ['done', 'skipped', 'skipped_auto'], true);
                $bCompleted = in_array($b->status, ['done', 'skipped', 'skipped_auto'], true);

                if ($aCompleted !== $bCompleted) {
                    return $aCompleted <=> $bCompleted;
                }

                if ($a->is_overdue !== $b->is_overdue) {
                    return ($a->is_overdue ? 0 : 1) <=> ($b->is_overdue ? 0 : 1);
                }

                return $a->id <=> $b->id;
            })
            ->values();
    }

    public function buildStats(Collection $instances): array
    {
        $total = $instances->count();
        $done = $instances->where('status', 'done')->count();
        $skipped = $instances->where('status', 'skipped')->count();
        $pending = $instances->where('status', 'pending')->count();
        $skipQuota = $this->gamification->calculateSkipQuota($total);

        return [
            'total' => $total,
            'done' => $done,
            'skipped' => $skipped,
            'pending' => $pending,
            'skip_quota' => $skipQuota,
            'remaining_skips' => max(0, $skipQuota - $skipped - $pending),
            'predicted_hp_change' => $this->gamification->predictHpChange($total, $done, $skipped, $pending),
        ];
    }
}
