<?php

namespace App\Actions;

use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use App\Services\TaskRecurrenceService;
use Carbon\Carbon;

class GenerateDailyTaskInstancesAction
{
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
}
