<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskInstance extends Model
{
    protected $fillable = [
        'task_template_id', 'user_id', 'scheduled_date',
        'status', 'note', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'task_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActionable(): bool
    {
        return in_array($this->status, ['pending', 'done', 'skipped']);
    }

    public function isOverdue(?\Carbon\Carbon $now = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $now = $now ?? now();

        if ($this->scheduled_date->toDateString() < $now->toDateString()) {
            return true;
        }

        $dueTime = $this->template?->due_time;

        if (! $dueTime) {
            return false;
        }

        $time = strlen((string) $dueTime) > 5
            ? substr((string) $dueTime, 0, 5)
            : (string) $dueTime;

        return $now->gt(\Carbon\Carbon::parse($this->scheduled_date->toDateString().' '.$time));
    }
}
