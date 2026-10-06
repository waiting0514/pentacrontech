<?php

namespace App\Http\Controllers\Api;

use App\Models\KnowledgeArticle;
use App\Traits\ApiResponse;
use App\Http\Resources\KnowledgeArticleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KnowledgeArticleController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('knowledge.view');
        $query = KnowledgeArticle::with('category');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('title->zh', 'like', "%{$keyword}%")
                  ->orWhere('title->en', 'like', "%{$keyword}%")
                  ->orWhere('excerpt->zh', 'like', "%{$keyword}%")
                  ->orWhere('excerpt->en', 'like', "%{$keyword}%")
                  ->orWhere('content->zh', 'like', "%{$keyword}%")
                  ->orWhere('content->en', 'like', "%{$keyword}%");
            });
        }

        $articles = $query->latest('published_at')->paginate(15);
        return $this->successResponse(KnowledgeArticleResource::collection($articles)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('knowledge.create');

        $validated = $request->validate([
            'title' => 'required|array',
            'title.zh-TW' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'slug' => 'nullable|array',
            'excerpt' => 'nullable|array',
            'content' => 'required|array',
            'content.zh-TW' => 'required|string',
            'content.en' => 'nullable|string',
            'image_url' => 'nullable|url',
            'schema_type' => 'nullable|in:article,faq,howto,tutorial',
            'category_id' => 'nullable|exists:knowledge_categories,id',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $article = KnowledgeArticle::create($validated);

        return $this->successResponse(new KnowledgeArticleResource($article->load('category')), 'Article created', 201);
    }

    public function show(KnowledgeArticle $knowledgeArticle): JsonResponse
    {
        $this->authorize('knowledge.view');
        return $this->successResponse(new KnowledgeArticleResource($knowledgeArticle->load('category')));
    }

    public function update(Request $request, KnowledgeArticle $knowledgeArticle): JsonResponse
    {
        $this->authorize('knowledge.update');

        $validated = $request->validate([
            'title' => 'sometimes|array',
            'title.zh-TW' => 'sometimes|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'slug' => 'nullable|array',
            'excerpt' => 'nullable|array',
            'content' => 'sometimes|array',
            'content.zh-TW' => 'sometimes|string',
            'content.en' => 'nullable|string',
            'image_url' => 'nullable|url',
            'schema_type' => 'nullable|in:article,faq,howto,tutorial',
            'category_id' => 'nullable|exists:knowledge_categories,id',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $knowledgeArticle->update($validated);

        return $this->successResponse(new KnowledgeArticleResource($knowledgeArticle->load('category')), 'Article updated');
    }

    public function destroy(KnowledgeArticle $knowledgeArticle): JsonResponse
    {
        $this->authorize('knowledge.delete');
        $knowledgeArticle->delete();

        return $this->successResponse(null, 'Article deleted');
    }
}
