<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySummary extends Model
{
    protected $fillable = [
        'user_id', 'date', 'total_tasks', 'done_count', 'skipped_count',
        'skipped_auto_count', 'skip_quota', 'over_skip', 'saved_skip',
        'hp_before', 'hp_change', 'hp_after', 'xp_earned', 'base_xp',
        'perf_multiplier', 'streak_multiplier', 'streak_before', 'streak_after',
        'shield_used', 'debt_added', 'debt_cleared', 'streak_reset',
        'pct_completed', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'closed_at' => 'datetime',
            'shield_used' => 'boolean',
            'debt_added' => 'boolean',
            'debt_cleared' => 'boolean',
            'streak_reset' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
