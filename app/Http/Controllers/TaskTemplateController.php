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

    public function edit(Request $request, TaskTemplate $task)
    {
        $this->authorizeTemplate($request, $task);
        $categories = Category::where('user_id', $request->user()->id)->orderBy('name')->get();

        return Inertia::render('Tasks/Form', [
            'template' => $task,
            'categories' => $categories,
        ]);
    }

    protected function authorizeTemplate(Request $request, TaskTemplate $task): void
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
