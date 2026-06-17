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

    public function execute(?Carbon $date = null): int
    {
        $date = $date ?? Carbon::today();
        $created = 0;

        $templates = TaskTemplate::where('is_active', true)->get();

        foreach ($templates as $template) {
            if (!$this->recurrenceService->shouldGenerateForDate($template, $date)) {
                continue;
            }

            $exists = TaskInstance::where('task_template_id', $template->id)
                ->where('scheduled_date', $date->toDateString())
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
