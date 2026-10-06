<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\KnowledgeCategory;
use App\Http\Resources\KnowledgeCategoryResource;
use Illuminate\Http\JsonResponse;

class KnowledgeCategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $this->authorize('knowledge.view');

        $categories = KnowledgeCategory::orderBy('sort_order')->orderBy('id')->get();
        return $this->successResponse(KnowledgeCategoryResource::collection($categories));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('knowledge.create');

        $validated = $request->validate([
            'name' => 'required|array',
            'name.zh-TW' => 'required|string|max:50',
            'name.en' => 'nullable|string|max:50',
            'slug' => 'nullable|array',
            'description' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $category = KnowledgeCategory::create($validated);

        return $this->successResponse(new KnowledgeCategoryResource($category), 'Category created', 201);
    }

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): JsonResponse
    {
        $this->authorize('knowledge.update');

        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.zh-TW' => 'sometimes|string|max:50',
            'name.en' => 'nullable|string|max:50',
            'slug' => 'nullable|array',
            'description' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $knowledgeCategory->update($validated);

        return $this->successResponse(new KnowledgeCategoryResource($knowledgeCategory), 'Category updated');
    }

    public function destroy(KnowledgeCategory $knowledgeCategory): JsonResponse
    {
        $this->authorize('knowledge.delete');

        if ($knowledgeCategory->articles()->count() > 0) {
            return $this->errorResponse('此分類下仍有知識庫文章，無法刪除', 'HAS_CHILDREN', 400);
        }

        $knowledgeCategory->delete();

        return $this->successResponse(null, '分類已刪除');
    }
}
