<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Http\JsonResponse;
use App\Services\ImageService;

class ProductCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('product.view');

        $categories = ProductCategory::orderBy('sort_order')->orderBy('id')->get();
        return $this->successResponse(ProductCategoryResource::collection($categories));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('product.create');

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|array',
            'name.zh-TW' => 'required|string|max:50',
            'name.en' => 'nullable|string|max:50',
            'slug' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->imageService->processCategoryImage($request->file('image'));
        }

        $category = ProductCategory::create($validated);

        return $this->successResponse(new ProductCategoryResource($category), 'Category created', 201);
    }

    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $this->authorize('product.update');

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'sometimes|array',
            'name.zh-TW' => 'sometimes|string|max:50',
            'name.en' => 'nullable|string|max:50',
            'slug' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        // 1. 處理取消父層：如果 parent_id 傳入 null 或空字串，將欄位更新為 null 變回最上層
        if (array_key_exists('parent_id', $validated)) {
            $validated['parent_id'] = empty($validated['parent_id']) ? null : $validated['parent_id'];
        }

        // 2. 檢查防止死鎖/循環關聯
        if (!empty($validated['parent_id'])) {
            $parentId = (int)$validated['parent_id'];
            
            // 不能選自己作為父層
            if ($parentId === $productCategory->id) {
                return $this->errorResponse('無法將自己設為父分類', 'INVALID_PARENT', 422);
            }
            
            // 不能選自己的子孫分類作為父層
            if ($this->isDescendantOf($parentId, $productCategory)) {
                return $this->errorResponse('無法將子分類設為父分類，這會造成循環關聯', 'LOOP_RELATION', 422);
            }
        }

        // System categories: remove protected fields from update
        if ($productCategory->isSystemCategory()) {
            unset($validated['slug']);
            unset($validated['is_system']);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($productCategory->image_path) {
                $this->imageService->deleteCategoryImage($productCategory->image_path);
            }
            $validated['image_path'] = $this->imageService->processCategoryImage($request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            // Delete old image and set null
            if ($productCategory->image_path) {
                $this->imageService->deleteCategoryImage($productCategory->image_path);
            }
            $validated['image_path'] = null;
        }

        $productCategory->update($validated);

        return $this->successResponse(new ProductCategoryResource($productCategory), 'Category updated');
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        $this->authorize('product.delete');

        // Prevent deletion of system categories
        if ($productCategory->isSystemCategory()) {
            return $this->errorResponse('系統分類無法刪除', 'SYSTEM_CATEGORY', 403);
        }

        // 【阻擋刪除策略】：檢查是否有子分類
        if ($productCategory->children()->exists()) {
            return $this->errorResponse('該分類下仍有子分類，無法直接刪除。請先移動或刪除底下的子分類。', 'HAS_CHILD_CATEGORIES', 422);
        }

        // Delete category image from storage if exists
        if ($productCategory->image_path) {
            $this->imageService->deleteCategoryImage($productCategory->image_path);
        }

        // Get default category for reassignment
        $defaultCategory = ProductCategory::getDefaultCategory();

        // Reassign products to default category
        $reassignedCount = 0;
        if ($defaultCategory) {
            $reassignedCount = $productCategory->products()->count();
            $productCategory->products()->update(['category_id' => $defaultCategory->id]);
        }

        $productCategory->delete();

        return $this->successResponse([
            'reassigned_products' => $reassignedCount,
        ], '分類已刪除');
    }

    /**
     * 遞迴檢查某個分類 ID 是否是指定分類的子孫
     */
    private function isDescendantOf(int $parentId, ProductCategory $category): bool
    {
        $childrenIds = $category->children()->pluck('id')->toArray();
        
        if (in_array($parentId, $childrenIds)) {
            return true;
        }
        
        foreach ($category->children as $child) {
            if ($this->isDescendantOf($parentId, $child)) {
                return true;
            }
        }
        
        return false;
    }
}
