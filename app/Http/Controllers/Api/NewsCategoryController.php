<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\NewsCategory;
use App\Http\Resources\NewsCategoryResource;
use Illuminate\Http\JsonResponse;

class NewsCategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $this->authorize('news.view');

        $categories = NewsCategory::orderBy('sort_order')->orderBy('id')->get();
        return $this->successResponse(NewsCategoryResource::collection($categories));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('news.create');

        $validated = $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:50',
            'name.zh-TW' => 'nullable|string|max:50',
            'slug' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $category = NewsCategory::create($validated);

        return $this->successResponse(new NewsCategoryResource($category), 'Category created', 201);
    }

    public function update(Request $request, NewsCategory $newsCategory): JsonResponse
    {
        $this->authorize('news.update');

        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:50',
            'name.zh-TW' => 'nullable|string|max:50',
            'slug' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $newsCategory->update($validated);

        return $this->successResponse($newsCategory, 'Category updated');
    }

    public function destroy(NewsCategory $newsCategory): JsonResponse
    {
        $this->authorize('news.delete');

        if ($newsCategory->news()->count() > 0) {
            return $this->errorResponse('此分類下仍有最新消息，無法刪除', 'HAS_CHILDREN', 400);
        }

        $newsCategory->delete();

        return $this->successResponse(null, '分類已刪除');
    }
}
