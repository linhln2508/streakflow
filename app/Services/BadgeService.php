<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\DailySummary;
use App\Models\User;

class BadgeService
{
    public function checkAndAward(User $user, DailySummary $summary): void
    {
        $checks = [
            'streak_7' => fn () => $user->streak_count >= 7,
            'streak_30' => fn () => $user->streak_count >= 30,
            'streak_100' => fn () => $user->streak_count >= 100,
            'perfect_day' => fn () => $summary->pct_completed >= 100,
            'level_5' => fn () => $user->level >= 5,
            'level_10' => fn () => $user->level >= 10,
            'hp_max' => fn () => $user->hp >= 100,
        ];

        foreach ($checks as $key => $check) {
            if ($check()) {
                $this->awardBadge($user, $key);
            }
        }

        $this->checkPerfectWeek($user);
    }

    protected function checkPerfectWeek(User $user): void
    {
        $summaries = DailySummary::where('user_id', $user->id)
            ->orderByDesc('date')
            ->limit(7)
            ->get();

        if ($summaries->count() < 7) {
            return;
        }

        $allPerfect = $summaries->every(fn ($s) => $s->pct_completed >= 100);

        if ($allPerfect) {
            $this->awardBadge($user, 'perfect_week');
        }
    }

    protected function awardBadge(User $user, string $key): void
    {
        $badge = Badge::where('key', $key)->first();

        if (!$badge) {
            return;
        }

        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        $user->badges()->attach($badge->id, ['earned_at' => now()]);
    }
}
