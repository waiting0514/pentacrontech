<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use App\Http\Resources\BannerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('banner.manage');
        $banners = Banner::orderBy('sort_order')->orderBy('id')->get();
        return $this->successResponse(BannerResource::collection($banners));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('banner.manage');
        $request->validate([
            'title' => 'required|array',
            'title.zh-TW' => 'required|string|max:255',
            'link_url' => 'nullable|array',
            'image_pc' => 'nullable|array',
            'image_pc.*' => 'image|max:5120',
            'image_mobile' => 'nullable|array',
            'image_mobile.*' => 'image|max:2048',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['title', 'link_url', 'sort_order', 'is_active']);
        
        $imagePc = [];
        if ($request->hasFile('image_pc')) {
            foreach ($request->file('image_pc') as $locale => $file) {
                $imagePc[$locale] = $this->imageService->processBannerImage($file, 'pc');
            }
        }
        $data['image_pc'] = $imagePc;

        $imageMobile = [];
        if ($request->hasFile('image_mobile')) {
            foreach ($request->file('image_mobile') as $locale => $file) {
                $imageMobile[$locale] = $this->imageService->processBannerImage($file, 'mobile');
            }
        }
        $data['image_mobile'] = $imageMobile;

        $banner = Banner::create($data);

        return $this->successResponse(new BannerResource($banner), 'Banner created', 201);
    }

    public function update(Request $request, Banner $banner): JsonResponse
    {
        $this->authorize('banner.manage');
        
        // 注意：PUT/PATCH 處理 MultipartFormData 在 PHP 有限制，通常建議用 POST + _method=PUT
        $request->validate([
            'title' => 'sometimes|array',
            'link_url' => 'nullable|array',
            'image_pc' => 'nullable|array',
            'image_pc.*' => 'image|max:5120',
            'image_mobile' => 'nullable|array',
            'image_mobile.*' => 'image|max:2048',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_pc')) {
            $currentImages = $banner->getTranslations('image_pc');
            foreach ($request->file('image_pc') as $locale => $file) {
                // 刪除舊圖 (如果有)
                if (!empty($currentImages[$locale])) {
                    $this->imageService->deleteBannerImages($currentImages[$locale], null);
                }
                $banner->setTranslation('image_pc', $locale, $this->imageService->processBannerImage($file, 'pc'));
            }
        }

        if ($request->hasFile('image_mobile')) {
            $currentImages = $banner->getTranslations('image_mobile');
            foreach ($request->file('image_mobile') as $locale => $file) {
                // 刪除舊圖 (如果有)
                if (!empty($currentImages[$locale])) {
                    $this->imageService->deleteBannerImages(null, $currentImages[$locale]);
                }
                $banner->setTranslation('image_mobile', $locale, $this->imageService->processBannerImage($file, 'mobile'));
            }
        }

        // 處理文字與連結
        if ($request->has('title')) $banner->title = $request->title;
        if ($request->has('link_url')) $banner->link_url = $request->link_url;
        if ($request->has('sort_order')) $banner->sort_order = $request->sort_order;
        if ($request->has('is_active')) $banner->is_active = $request->boolean('is_active');

        $banner->save();

        return $this->successResponse(new BannerResource($banner), 'Banner updated');
    }

    public function updateSort(Request $request, Banner $banner): JsonResponse
    {
        $this->authorize('banner.manage');
        $request->validate([
            'sort_order' => 'required|integer',
        ]);

        $banner->update(['sort_order' => $request->sort_order]);

        return $this->successResponse(new BannerResource($banner), 'Sort order updated');
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $this->authorize('banner.manage');
        
        // 刪除所有語系的圖片
        foreach ($banner->getTranslations('image_pc') as $path) {
            $this->imageService->deleteBannerImages($path, null);
        }
        foreach ($banner->getTranslations('image_mobile') as $path) {
            $this->imageService->deleteBannerImages(null, $path);
        }
        
        $banner->delete();

        return $this->successResponse(null, 'Banner 已刪除');
    }
}