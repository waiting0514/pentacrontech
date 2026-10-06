<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use App\Traits\ApiResponse;
use App\Http\Resources\NewsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('news.view');
        $query = News::with('category');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('title->zh', 'like', "%{$keyword}%")
                  ->orWhere('title->en', 'like', "%{$keyword}%")
                  ->orWhere('content->zh', 'like', "%{$keyword}%")
                  ->orWhere('content->en', 'like', "%{$keyword}%");
            });
        }

        $news = $query->latest('published_at')->paginate(15);
        return $this->successResponse(NewsResource::collection($news)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('news.create');
        $validated = $request->validate([
            'title' => 'required|array',
            'title.zh-TW' => 'required|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'slug' => 'nullable|array',
            'content' => 'required|array',
            'content.zh-TW' => 'required|string',
            'content.en' => 'nullable|string',
            'image_url' => 'nullable|url',
            'category_id' => 'nullable|exists:news_categories,id',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $news = News::create($validated);

        return $this->successResponse(new NewsResource($news->load('category')), 'News created', 201);
    }

    public function show(News $news): JsonResponse
    {
        $this->authorize('news.view');
        return $this->successResponse(new NewsResource($news->load('category')));
    }

    public function update(Request $request, News $news): JsonResponse
    {
        $this->authorize('news.update');
        $validated = $request->validate([
            'title' => 'sometimes|array',
            'title.zh-TW' => 'sometimes|string|max:255',
            'title.en' => 'nullable|string|max:255',
            'slug' => 'nullable|array',
            'content' => 'sometimes|array',
            'content.zh-TW' => 'sometimes|string',
            'content.en' => 'nullable|string',
            'image_url' => 'nullable|url',
            'category_id' => 'nullable|exists:news_categories,id',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $news->update($validated);

        return $this->successResponse(new NewsResource($news->load('category')), 'News updated');
    }

    public function destroy(News $news): JsonResponse
    {
        $this->authorize('news.delete');
        $news->delete();

        return $this->successResponse(null, 'News deleted');
    }
}
