<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskTemplate::with('category')
            ->where('user_id', $request->user()->id);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('recurrence_type')) {
            $query->where('recurrence_type', $request->recurrence_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $templates = $query->orderBy('sort_order')->orderBy('title')->get();
        $categories = Category::where('user_id', $request->user()->id)->orderBy('name')->get();

        return Inertia::render('Tasks/Index', [
            'templates' => $templates,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'recurrence_type', 'is_active']),
        ]);
    }

    public function create(Request $request)
    {
        $categories = Category::where('user_id', $request->user()->id)->orderBy('name')->get();

        return Inertia::render('Tasks/Form', [
            'template' => null,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);

        TaskTemplate::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task đã được tạo.');
    }

    public function edit(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $categories = Category::where('user_id', $request->user()->id)->orderBy('name')->get();

        return Inertia::render('Tasks/Form', [
            'template' => $task,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $validated = $this->validateTemplate($request);
        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task đã được cập nhật.');
    }

    public function destroy(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task đã được xóa.');
    }

    public function toggle(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $task->update(['is_active' => !$task->is_active]);

        return back();
    }

    protected function validateTemplate(Request $request): array
    {
        $validated = $request->validate([
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

        if ($validated['recurrence_type'] === 'one_time') {
            $request->validate(['recurrence_config.date' => 'required|date']);
        }

        if ($validated['recurrence_type'] === 'weekly') {
            $request->validate(['recurrence_config.days' => 'required|array|min:1']);
        }

        if ($validated['recurrence_type'] === 'monthly') {
            $request->validate(['recurrence_config.days' => 'required|array|min:1']);
        }

        if ($validated['recurrence_type'] === 'custom') {
            $request->validate(['recurrence_config.interval' => 'required|integer|min:1']);
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
