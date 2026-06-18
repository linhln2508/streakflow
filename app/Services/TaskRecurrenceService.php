<?php

namespace App\Services;

use App\Models\TaskTemplate;
use Carbon\Carbon;

class TaskRecurrenceService
{
    public function shouldGenerateForDate(TaskTemplate $template, Carbon $date): bool
    {
        if (!$template->is_active) {
            return false;
        }

        if ($date->toDateString() < $template->start_date->toDateString()) {
            return false;
        }

        if ($template->end_date && $date->toDateString() > $template->end_date->toDateString()) {
            return false;
        }

        $config = $template->recurrence_config ?? [];

        return match ($template->recurrence_type) {
            'daily' => true,
            'weekdays' => $date->isWeekday(),
            'weekly' => in_array($date->dayOfWeekIso, $config['days'] ?? []),
            'monthly' => in_array($date->day, $config['days'] ?? []),
            'custom' => $this->matchesCustomInterval($template, $date, $config),
            'one_time' => isset($config['date']) && $date->toDateString() === $config['date'],
            default => false,
        };
    }

    protected function matchesCustomInterval(TaskTemplate $template, Carbon $date, array $config): bool
    {
        $interval = $config['interval'] ?? 1;
        $startDate = $template->start_date;
        $daysDiff = $startDate->diffInDays($date);

        return $daysDiff % $interval === 0;
    }
}
