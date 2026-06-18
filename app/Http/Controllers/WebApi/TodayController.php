<?php

namespace App\Http\Controllers\WebApi;

use App\Actions\CloseDaySummaryAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Models\DailySummary;
use App\Models\TaskInstance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TodayController extends Controller
{
    use RespondsWithJsonApi;

    public function done(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureDayNotClosed($request);

        $instance->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        return $this->jsonSuccess($instance->fresh()->load('template.category'), __('today.done'));
    }

    public function skip(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureDayNotClosed($request);

        $instance->update([
            'status' => 'skipped',
            'completed_at' => now(),
        ]);

        return $this->jsonSuccess($instance->fresh()->load('template.category'), __('today.skipped'));
    }

    public function undo(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureDayNotClosed($request);

        $instance->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return $this->jsonSuccess($instance->fresh()->load('template.category'), __('today.undone'));
    }

    public function close(Request $request, CloseDaySummaryAction $action)
    {
        $user = $request->user();
        $today = Carbon::today();

        $exists = DailySummary::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        if ($exists) {
            return $this->jsonFail(__('today.already_closed'), 422);
        }

        $summary = $action->execute($user->id, $today, 'user');

        return $this->jsonSuccess([
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
        ], __('today.closed'));
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
            ->whereDate('date', Carbon::today())
            ->exists();

        if ($closed) {
            abort(403, __('today.day_closed'));
        }
    }
}
