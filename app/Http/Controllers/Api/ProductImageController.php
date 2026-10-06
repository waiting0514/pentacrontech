<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    private function formatImage(ProductImage $img): array
    {
        return [
            'id' => $img->id,
            'paths' => [
                'main' => $img->paths['main'] ? asset('storage/' . $img->paths['main']) : null,
                'thumb' => $img->paths['thumb'] ? asset('storage/' . $img->paths['thumb']) : null,
                'tiny' => $img->paths['tiny'] ? asset('storage/' . $img->paths['tiny']) : null,
            ],
            'is_primary' => $img->is_primary,
            'sort_order' => $img->sort_order,
        ];
    }

    public function upload(Request $request, Product $product): JsonResponse
    {
        $this->authorize('product.update');

        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'images.max' => '單次最多只能上傳 10 張圖片',
            'images.*.mimes' => '圖片格式必須為 jpg、jpeg、png 或 webp',
            'images.*.max' => '圖片大小不能超過 2MB',
        ]);

        $uploaded = [];
        $hasImages = $product->images()->exists();

        foreach ($request->file('images') as $index => $image) {
            $paths = $this->imageService->processProductImage($image);

            $productImage = $product->images()->create([
                'paths' => $paths,
                'is_primary' => !$hasImages && $index === 0,
                'sort_order' => $product->images()->count(),
            ]);

            $uploaded[] = $this->formatImage($productImage);
            $hasImages = true;
        }

        return $this->successResponse($uploaded, 'Images uploaded', 201);
    }

    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        $this->authorize('product.update');

        if ($image->product_id !== $product->id) {
            return $this->errorResponse('圖片不屬於此產品', 'FORBIDDEN', 403);
        }

        $this->imageService->deleteProductImage($image->paths);

        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary) {
            $next = $product->images()->orderBy('sort_order')->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return $this->successResponse(null, '圖片已刪除');
    }

    public function setPrimary(Product $product, ProductImage $image): JsonResponse
    {
        $this->authorize('product.update');

        if ($image->product_id !== $product->id) {
            return $this->errorResponse('圖片不屬於此產品', 'FORBIDDEN', 403);
        }

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return $this->successResponse($this->formatImage($image->fresh()), 'Primary image set');
    }

    public function reorder(Request $request, Product $product): JsonResponse
    {
        $this->authorize('product.update');

        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'exists:product_images,id',
        ]);

        foreach ($request->image_ids as $index => $id) {
            ProductImage::where('id', $id)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }

        return $this->successResponse(null, '排序已更新');
    }
}
