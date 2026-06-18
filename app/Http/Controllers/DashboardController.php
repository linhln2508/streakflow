<?php

namespace App\Http\Controllers;

use App\Actions\GenerateDailyTaskInstancesAction;
use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Services\DayViewService;
use App\Services\GamificationService;
use App\Services\UnclosedDaysService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected GamificationService $gamification,
        protected UnclosedDaysService $unclosedDays,
        protected DayViewService $dayView,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        $todayString = $today->toDateString();

        app(GenerateDailyTaskInstancesAction::class)->execute($today, $user->id);

        $unclosedDays = $this->unclosedDays->forUser($user->id)->values();
        $selectedDate = $this->resolveSelectedDate($request, $unclosedDays, $today);

        if ($this->unclosedDays->isClosed($user->id, $selectedDate->toDateString())) {
            return redirect()->route('reports.day', $selectedDate->toDateString());
        }

        $hasTasks = TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', $selectedDate)
            ->exists();

        if (!$hasTasks && !$selectedDate->isToday()) {
            return redirect()->route('dashboard');
        }

        $instances = $this->dayView->loadInstances($user->id, $selectedDate);
        $isDayClosed = false;

        return Inertia::render('Dashboard', [
            'instances' => $instances,
            'stats' => $this->dayView->buildStats($instances),
            'isDayClosed' => $isDayClosed,
            'selectedDate' => $selectedDate->toDateString(),
            'today' => $todayString,
            'isToday' => $selectedDate->isToday(),
            'unclosedDays' => $unclosedDays,
            'xpToNextLevel' => $this->gamification->xpToNextLevel($user->xp ?? 0, $user->level ?? 1),
        ]);
    }

    protected function resolveSelectedDate(Request $request, $unclosedDays, Carbon $today): Carbon
    {
        if ($request->filled('date')) {
            $date = Carbon::parse($request->query('date'));

            if ($date->isFuture()) {
                return $today;
            }

            return $date;
        }

        $oldestPast = $unclosedDays->first(fn ($day) => !$day['is_today']);

        return $oldestPast
            ? Carbon::parse($oldestPast['date'])
            : $today;
    }
}
