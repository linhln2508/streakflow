<?php

namespace App\Http\Controllers\WebApi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use RespondsWithJsonApi;

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $category = Category::create([
            ...$validator->validated(),
            'user_id' => $request->user()->id,
        ]);

        return $this->jsonSuccess($category, __('categories.created'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($request, $category);

        $validator = validator($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $category->update($validator->validated());

        return $this->jsonSuccess($category->fresh(), __('categories.updated'));
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorizeCategory($request, $category);
        $category->delete();

        return $this->jsonSuccess(null, __('categories.deleted'));
    }

    protected function authorizeCategory(Request $request, Category $category): void
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
