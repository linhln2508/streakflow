<?php

namespace App\Http\Controllers;

use App\Actions\CloseDaySummaryAction;
use App\Models\DailySummary;
use App\Models\TaskInstance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TodayController extends Controller
{
    public function done(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureDayNotClosed($request);

        $instance->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        return back();
    }

    public function skip(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureDayNotClosed($request);

        $instance->update([
            'status' => 'skipped',
            'completed_at' => now(),
        ]);

        return back();
    }

    public function undo(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureDayNotClosed($request);

        $instance->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return back();
    }

    public function close(Request $request, CloseDaySummaryAction $action)
    {
        $user = $request->user();
        $today = Carbon::today();

        $exists = DailySummary::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ngày hôm nay đã được chốt.');
        }

        $summary = $action->execute($user->id, $today, 'user');

        return back()->with('closeResult', [
            'hp_change' => $summary->hp_change,
            'hp_after' => $summary->hp_after,
            'xp_earned' => $summary->xp_earned,
            'streak_before' => $summary->streak_before,
            'streak_after' => $summary->streak_after,
            'pct_completed' => $summary->pct_completed,
            'shield_used' => $summary->shield_used,
            'debt_added' => $summary->debt_added,
            'streak_reset' => $summary->streak_reset,
            'done_count' => $summary->done_count,
            'total_tasks' => $summary->total_tasks,
        ]);
    }

    protected function authorizeInstance(Request $request, TaskInstance $instance): void
    {
        if ($instance->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function ensureDayNotClosed(Request $request): void
    {
        $closed = DailySummary::where('user_id', $request->user()->id)
            ->where('date', Carbon::today()->toDateString())
            ->exists();

        if ($closed) {
            abort(403, 'Ngày đã được chốt.');
        }
    }
}
