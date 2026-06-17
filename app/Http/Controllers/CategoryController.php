<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        Category::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Danh mục đã được tạo.');
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($request, $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        $category->update($validated);

        return back()->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorizeCategory($request, $category);
        $category->delete();

        return back()->with('success', 'Danh mục đã được xóa.');
    }

    protected function authorizeCategory(Request $request, Category $category): void
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
