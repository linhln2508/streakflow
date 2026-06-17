<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected GamificationService $gamification,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $isDayClosed = DailySummary::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        $instances = TaskInstance::with(['template.category'])
            ->where('user_id', $user->id)
            ->where('scheduled_date', $today)
            ->orderBy('id')
            ->get();

        $total = $instances->count();
        $done = $instances->where('status', 'done')->count();
        $skipped = $instances->where('status', 'skipped')->count();
        $pending = $instances->where('status', 'pending')->count();
        $skipQuota = $this->gamification->calculateSkipQuota($total);
        $usedSkips = $skipped;
        $remainingSkips = max(0, $skipQuota - $usedSkips - $pending);
        $predictedHpChange = $this->gamification->predictHpChange($total, $done, $skipped, $pending);

        return Inertia::render('Dashboard', [
            'instances' => $instances,
            'stats' => [
                'total' => $total,
                'done' => $done,
                'skipped' => $skipped,
                'pending' => $pending,
                'skip_quota' => $skipQuota,
                'remaining_skips' => $remainingSkips,
                'predicted_hp_change' => $predictedHpChange,
            ],
            'isDayClosed' => $isDayClosed,
            'today' => $today,
            'xpToNextLevel' => $this->gamification->xpToNextLevel($user->xp, $user->level),
        ]);
    }
}
