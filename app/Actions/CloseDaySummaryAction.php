<?php

namespace App\Actions;

use App\Models\DailySummary;
use App\Models\ShieldLog;
use App\Models\TaskInstance;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CloseDaySummaryAction
{
    public function __construct(
        protected GamificationService $gamification,
        protected BadgeService $badgeService,
    ) {}

    public function execute(int $userId, Carbon $date, string $closedBy = 'user'): DailySummary
    {
        $user = User::findOrFail($userId);
        $dateStr = $date->toDateString();

        $existing = DailySummary::where('user_id', $userId)->whereDate('date', $dateStr)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $date, $dateStr, $closedBy) {
            $instances = TaskInstance::where('user_id', $user->id)
                ->whereDate('scheduled_date', $dateStr)
                ->lockForUpdate()
                ->get();

            foreach ($instances as $instance) {
                if ($instance->status === 'pending') {
                    $instance->update(['status' => 'skipped_auto']);
                }
            }

            $instances = $instances->fresh();

            $total = $instances->count();
            $done = $instances->where('status', 'done')->count();
            $skipped = $instances->where('status', 'skipped')->count();
            $skippedAuto = $instances->where('status', 'skipped_auto')->count();

            $hpData = $this->gamification->calculateHpChange($total, $skipped, $skippedAuto);
            $hpBefore = $user->hp;
            $hpAfter = $this->gamification->calculateHpAfter($hpBefore, $hpData['hp_change']);
            $pctCompleted = $this->gamification->calculateCompletionPercent($done, $total);

            $streakResult = $this->gamification->processStreak(
                $user->streak_count,
                $user->shield_count,
                $user->debt_count,
                $pctCompleted
            );

            $xpData = $this->gamification->calculateXpEarned(
                $total,
                $pctCompleted,
                $user->streak_count,
                $hpBefore,
                $hpData['hp_change']
            );

            $newXp = $user->xp + $xpData['xp_earned'];
            $newLevel = $this->gamification->calculateLevel($newXp);

            $summary = DailySummary::create([
                'user_id' => $user->id,
                'date' => $dateStr,
                'total_tasks' => $total,
                'done_count' => $done,
                'skipped_count' => $skipped,
                'skipped_auto_count' => $skippedAuto,
                'skip_quota' => $hpData['skip_quota'],
                'over_skip' => $hpData['over_skip'],
                'saved_skip' => $hpData['saved_skip'],
                'hp_before' => $hpBefore,
                'hp_change' => $hpData['hp_change'],
                'hp_after' => $hpAfter,
                'xp_earned' => $xpData['xp_earned'],
                'base_xp' => $xpData['base_xp'],
                'perf_multiplier' => $xpData['perf_multiplier'],
                'streak_multiplier' => $xpData['streak_multiplier'],
                'streak_before' => $user->streak_count,
                'streak_after' => $streakResult['streak_after'],
                'shield_used' => $streakResult['shield_used'],
                'debt_added' => $streakResult['debt_added'],
                'debt_cleared' => $streakResult['debt_cleared'],
                'streak_reset' => $streakResult['streak_reset'],
                'pct_completed' => $pctCompleted,
                'closed_by' => $closedBy,
                'closed_at' => now(),
            ]);

            $user->update([
                'hp' => $hpAfter,
                'xp' => $newXp,
                'level' => $newLevel,
                'streak_count' => $streakResult['streak_after'],
                'streak_last_date' => $dateStr,
                'shield_count' => $streakResult['shield_count'],
                'debt_count' => $streakResult['debt_count'],
            ]);

            if ($streakResult['shield_used']) {
                ShieldLog::create([
                    'user_id' => $user->id,
                    'action' => 'used',
                    'reason' => 'streak_protection',
                    'date' => $dateStr,
                ]);
            }

            if ($streakResult['shield_earned']) {
                ShieldLog::create([
                    'user_id' => $user->id,
                    'action' => 'earned',
                    'reason' => 'perfect_day',
                    'date' => $dateStr,
                ]);
            }

            $this->badgeService->checkAndAward($user->fresh(), $summary);

            return $summary;
        });
    }
}
