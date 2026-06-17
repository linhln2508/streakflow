<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        protected GamificationService $gamification,
    ) {}

    public function day(Request $request, string $date)
    {
        $user = $request->user();
        $summary = DailySummary::where('user_id', $user->id)->where('date', $date)->first();

        $instances = TaskInstance::with('template.category')
            ->where('user_id', $user->id)
            ->where('scheduled_date', $date)
            ->get();

        return Inertia::render('Reports/Day', [
            'date' => $date,
            'summary' => $summary,
            'instances' => $instances,
        ]);
    }

    public function week(Request $request, int $year, int $week)
    {
        $user = $request->user();
        $start = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $summaries = DailySummary::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $totalHpChange = $summaries->sum('hp_change');
        $totalXpEarned = $summaries->sum('xp_earned');

        return Inertia::render('Reports/Week', [
            'year' => $year,
            'week' => $week,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'summaries' => $summaries,
            'totalHpChange' => $totalHpChange,
            'totalXpEarned' => $totalXpEarned,
        ]);
    }

    public function month(Request $request, int $year, int $month)
    {
        $user = $request->user();
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $summaries = DailySummary::where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($s) => $s->date->format('Y-m-d'));

        $skippedTasks = TaskInstance::with('template')
            ->where('user_id', $user->id)
            ->whereBetween('scheduled_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('status', ['skipped', 'skipped_auto'])
            ->get()
            ->groupBy('task_template_id')
            ->map(fn ($group) => [
                'title' => $group->first()->template->title,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(10);

        return Inertia::render('Reports/Month', [
            'year' => $year,
            'month' => $month,
            'summaries' => $summaries,
            'topSkipped' => $skippedTasks,
        ]);
    }

    public function overview(Request $request)
    {
        $user = $request->user()->load('badges');

        $longestStreak = DailySummary::where('user_id', $user->id)
            ->max('streak_after') ?? 0;

        return Inertia::render('Reports/Overview', [
            'user' => $user,
            'xpToNextLevel' => $this->gamification->xpToNextLevel($user->xp, $user->level),
            'xpForNextLevel' => $this->gamification->xpRequiredForLevel($user->level + 1),
            'longestStreak' => $longestStreak,
            'badges' => $user->badges,
        ]);
    }
}
