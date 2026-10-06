<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Traits\ApiResponse;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\ImageService;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('product.view');
        $query = Product::with(['images', 'category']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name->zh', 'like', "%{$keyword}%")
                  ->orWhere('name->en', 'like', "%{$keyword}%")
                  ->orWhere('description->zh', 'like', "%{$keyword}%")
                  ->orWhere('description->en', 'like', "%{$keyword}%")
                  ->orWhere('specs', 'like', "%{$keyword}%");
            });
        }

        $products = $query->paginate(15);
        return $this->successResponse(ProductResource::collection($products)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('product.create');
        $validated = $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|array',
            'name.zh-TW' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.zh-TW' => 'nullable|string',
            'description.en' => 'nullable|string',
            'specs' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_inquiry_only' => 'boolean',
            'slug' => 'nullable|array',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'images.max' => '單次最多只能上傳 10 張圖片',
            'images.*.mimes' => '圖片格式必須為 jpg、jpeg、png 或 webp',
            'images.*.max' => '圖片大小不能超過 2MB',
        ]);

        $productData = collect($validated)->except(['images'])->toArray();
        $product = Product::create($productData);

        if ($request->hasFile('images')) {
            $hasImages = false;
            foreach ($request->file('images') as $index => $image) {
                $paths = $this->imageService->processProductImage($image);
                $product->images()->create([
                    'paths' => $paths,
                    'is_primary' => !$hasImages && $index === 0,
                    'sort_order' => $product->images()->count(),
                ]);
                $hasImages = true;
            }
        }

        return $this->successResponse(new ProductResource($product->load('images')), 'Product created', 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('product.view');
        return $this->successResponse(new ProductResource($product->load('images')));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('product.update');
        $validated = $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'sometimes|array',
            'name.zh-TW' => 'sometimes|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.zh-TW' => 'nullable|string',
            'description.en' => 'nullable|string',
            'specs' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'is_inquiry_only' => 'boolean',
            'slug' => 'nullable|array',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
        ]);

        $product->update($validated);

        return $this->successResponse(new ProductResource($product->load('images')), 'Product updated');
    }

    public function batchAction(Request $request): JsonResponse
    {
        $this->authorize('product.update');
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'action' => 'required|string|in:set_inquiry_mode',
            'value' => 'required|boolean',
        ]);

        $query = Product::whereIn('id', $validated['ids']);
        $count = 0;

        if ($validated['action'] === 'set_inquiry_mode') {
            $count = $query->update(['is_inquiry_only' => $validated['value']]);
        }

        return $this->successResponse(
            ['count' => $count], 
            'Batch action completed successfully'
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('product.delete');
        $product->delete();

        return $this->successResponse(null, 'Product deleted');
    }
}
