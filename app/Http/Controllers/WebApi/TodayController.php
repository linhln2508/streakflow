<?php

namespace App\Http\Controllers\WebApi;

use App\Actions\CloseDaySummaryAction;
use App\Actions\GenerateDailyTaskInstancesAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TodayController extends Controller
{
    use RespondsWithJsonApi;

    public function done(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureInstanceDateOpen($request, $instance);

        $instance->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        return $this->jsonSuccess($instance->fresh()->load('template.category'), __('today.done'));
    }

    public function skip(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureInstanceDateOpen($request, $instance);

        $instance->update([
            'status' => 'skipped',
            'completed_at' => now(),
        ]);

        return $this->jsonSuccess($instance->fresh()->load('template.category'), __('today.skipped'));
    }

    public function undo(Request $request, TaskInstance $instance)
    {
        $this->authorizeInstance($request, $instance);
        $this->ensureInstanceDateOpen($request, $instance);

        $instance->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return $this->jsonSuccess($instance->fresh()->load('template.category'), __('today.undone'));
    }

    public function quickTask(Request $request)
    {
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'priority' => 'nullable|in:low,medium,high',
            'due_time' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $this->ensureTodayOpen($request);

        $validated = $validator->validated();
        $today = Carbon::today()->toDateString();

        TaskTemplate::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'priority' => $validated['priority'] ?? 'medium',
            'recurrence_type' => 'one_time',
            'recurrence_config' => ['date' => $today],
            'start_date' => $today,
            'due_time' => $validated['due_time'] ?? null,
            'is_active' => true,
        ]);

        app(GenerateDailyTaskInstancesAction::class)->execute(Carbon::today(), $request->user()->id);

        return $this->jsonSuccess(null, __('today.quick_task_created'));
    }

    public function close(Request $request, CloseDaySummaryAction $action)
    {
        $validator = validator($request->all(), [
            'date' => 'nullable|date_format:Y-m-d|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $user = $request->user();
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();
        $dateStr = $date->toDateString();

        if (DailySummary::where('user_id', $user->id)->whereDate('date', $dateStr)->exists()) {
            return $this->jsonFail(__('today.already_closed'), 422);
        }

        $hasTasks = TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', $dateStr)
            ->exists();

        if (!$hasTasks) {
            return $this->jsonFail(__('today.no_tasks_to_close'), 422);
        }

        $summary = $action->execute($user->id, $date, 'user');

        return $this->jsonSuccess([
            'date' => $dateStr,
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

    protected function ensureTodayOpen(Request $request): void
    {
        $this->ensureDateOpen($request->user()->id, Carbon::today());
    }

    protected function ensureInstanceDateOpen(Request $request, TaskInstance $instance): void
    {
        $this->ensureDateOpen(
            $request->user()->id,
            Carbon::parse($instance->scheduled_date),
        );
    }

    protected function ensureDateOpen(int $userId, Carbon $date): void
    {
        $closed = DailySummary::where('user_id', $userId)
            ->whereDate('date', $date)
            ->exists();

        if ($closed) {
            abort(403, __('today.day_closed'));
        }
    }
}
