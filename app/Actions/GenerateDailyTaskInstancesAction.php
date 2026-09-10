<?php

namespace App\Actions;

use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use App\Services\TaskRecurrenceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GenerateDailyTaskInstancesAction
{
    /** Max days to look back when filling gaps (safety cap). */
    public const BACKFILL_LIMIT_DAYS = 30;

    public function __construct(
        protected TaskRecurrenceService $recurrenceService,
    ) {}

    public function execute(?Carbon $date = null, ?int $userId = null): int
    {
        $date = $date ?? Carbon::today();
        $created = 0;

        $templates = TaskTemplate::query()
            ->where('is_active', true)
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->get();

        foreach ($templates as $template) {
            if (!$this->recurrenceService->shouldGenerateForDate($template, $date)) {
                continue;
            }

            $exists = TaskInstance::where('task_template_id', $template->id)
                ->whereDate('scheduled_date', $date)
                ->exists();

            if ($exists) {
                continue;
            }

            TaskInstance::create([
                'task_template_id' => $template->id,
                'user_id' => $template->user_id,
                'scheduled_date' => $date->toDateString(),
                'status' => 'pending',
            ]);

            $created++;

            if ($template->recurrence_type === 'one_time') {
                $template->update(['is_active' => false]);
            }
        }

        return $created;
    }

    /**
     * Generate instances for today and any missing prior days so gaps
     * (e.g. skipped yesterday) are filled instead of permanently lost.
     */
    public function executeWithBackfill(?Carbon $through = null, ?int $userId = null): int
    {
        $through = ($through ?? Carbon::today())->copy()->startOfDay();
        $created = 0;

        $userIds = $this->resolveUserIds($userId);

        foreach ($userIds as $uid) {
            $from = $this->resolveBackfillStart((int) $uid, $through);

            for ($date = $from->copy(); $date->lte($through); $date->addDay()) {
                $created += $this->execute($date->copy(), (int) $uid);
            }
        }

        return $created;
    }

    protected function resolveUserIds(?int $userId): Collection
    {
        if ($userId !== null) {
            return collect([$userId]);
        }

        return TaskTemplate::query()
            ->where('is_active', true)
            ->distinct()
            ->pluck('user_id');
    }

    /**
     * Start from the day after the last known activity (instance or closed summary).
     * If the user has never had data, only generate for $through (today).
     */
    protected function resolveBackfillStart(int $userId, Carbon $through): Carbon
    {
        $lastInstance = TaskInstance::query()
            ->where('user_id', $userId)
            ->max('scheduled_date');

        $lastSummary = DailySummary::query()
            ->where('user_id', $userId)
            ->max('date');

        $candidates = array_filter([$lastInstance, $lastSummary]);

        if ($candidates === []) {
            return $through->copy();
        }

        $start = Carbon::parse(max($candidates))->startOfDay()->addDay();
        $earliestAllowed = $through->copy()->subDays(self::BACKFILL_LIMIT_DAYS);

        if ($start->lt($earliestAllowed)) {
            $start = $earliestAllowed;
        }

        return $start->gt($through) ? $through->copy() : $start;
    }
}
