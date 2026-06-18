<?php

namespace App\Http\Controllers\WebApi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;

class TaskTemplateController extends Controller
{
    use RespondsWithJsonApi;

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);
        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        $template = TaskTemplate::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return $this->jsonSuccess($template->load('category'), __('tasks.created'));
    }

    public function update(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);

        $validated = $this->validateTemplate($request);
        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        $task->update($validated);

        return $this->jsonSuccess($task->fresh()->load('category'), __('tasks.updated'));
    }

    public function destroy(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $task->delete();

        return $this->jsonSuccess(null, __('tasks.deleted'));
    }

    public function toggle(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $task->update(['is_active' => !$task->is_active]);

        return $this->jsonSuccess($task->fresh(), __('tasks.toggled'));
    }

    protected function validateTemplate(Request $request): array|\Illuminate\Http\JsonResponse
    {
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'priority' => 'required|in:low,medium,high',
            'recurrence_type' => 'required|in:daily,weekly,monthly,weekdays,custom,one_time',
            'recurrence_config' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $validated = $validator->validated();

        if ($validated['recurrence_type'] === 'one_time') {
            $extra = validator($request->all(), ['recurrence_config.date' => 'required|date']);
            if ($extra->fails()) {
                return $this->jsonValidationError($extra);
            }
        }

        if ($validated['recurrence_type'] === 'weekly') {
            $extra = validator($request->all(), ['recurrence_config.days' => 'required|array|min:1']);
            if ($extra->fails()) {
                return $this->jsonValidationError($extra);
            }
        }

        if ($validated['recurrence_type'] === 'monthly') {
            $extra = validator($request->all(), ['recurrence_config.days' => 'required|array|min:1']);
            if ($extra->fails()) {
                return $this->jsonValidationError($extra);
            }
        }

        if ($validated['recurrence_type'] === 'custom') {
            $extra = validator($request->all(), ['recurrence_config.interval' => 'required|integer|min:1']);
            if ($extra->fails()) {
                return $this->jsonValidationError($extra);
            }
        }

        return $validated;
    }

    protected function authorizeTemplate(Request $request, TaskTemplate $task): void
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
