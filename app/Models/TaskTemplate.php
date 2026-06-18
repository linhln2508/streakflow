<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskTemplate extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'priority',
        'recurrence_type', 'recurrence_config', 'start_date', 'end_date', 'due_time',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'recurrence_config' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(TaskInstance::class);
    }
}
