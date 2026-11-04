<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AiTextController;
use App\Http\Controllers\Api\AiImageController;
use App\Http\Controllers\Api\UsageController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

// Public routes
Route::post('/login', [AuthController::class, 'createToken']);

// Languages (public - needed for frontend forms)
Route::get('/languages', [App\Http\Controllers\Api\LanguageController::class, 'index']);

// Translations (public - needed for frontend/admin i18n)
Route::get('/translations/{locale}', [App\Http\Controllers\Api\TranslationController::class, 'index']);

// Protected routes (admin only - customer role is blocked)
Route::middleware(['auth:sanctum', 'active', 'role:super-admin|admin|manager|editor'])->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('roles');
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
        ]);
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Site settings (single-tenant)
    Route::prefix('settings')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\SiteSettingController::class, 'show']);
        Route::put('/', [App\Http\Controllers\Api\SiteSettingController::class, 'update']);
        Route::post('/test-smtp', [App\Http\Controllers\Api\SiteSettingController::class, 'testSmtp']);
    });

    // Product routes (API)
    Route::post('/products/batch-action', [ProductController::class, 'batchAction']);
    Route::apiResource('products', ProductController::class)->names([
        'index' => 'api.products.index',
        'store' => 'api.products.store',
        'show' => 'api.products.show',
        'update' => 'api.products.update',
        'destroy' => 'api.products.destroy',
    ]);

    // News routes (API)
    Route::apiResource('news', App\Http\Controllers\Api\NewsController::class)->names([
        'index' => 'api.news.index',
        'store' => 'api.news.store',
        'show' => 'api.news.show',
        'update' => 'api.news.update',
        'destroy' => 'api.news.destroy',
    ]);

    Route::apiResource('news-categories', App\Http\Controllers\Api\NewsCategoryController::class);

    Route::apiResource('product-categories', App\Http\Controllers\Api\ProductCategoryController::class);

    // Product images
    Route::post('/products/{product}/images', [App\Http\Controllers\Api\ProductImageController::class, 'upload']);
    Route::delete('/products/{product}/images/{image}', [App\Http\Controllers\Api\ProductImageController::class, 'destroy']);
    Route::put('/products/{product}/images/{image}/primary', [App\Http\Controllers\Api\ProductImageController::class, 'setPrimary']);
    Route::put('/products/{product}/images/reorder', [App\Http\Controllers\Api\ProductImageController::class, 'reorder']);

    // Banners
    Route::get('/banners', [App\Http\Controllers\Api\BannerController::class, 'index']);
    Route::post('/banners', [App\Http\Controllers\Api\BannerController::class, 'store']);
    Route::put('/banners/{banner}', [App\Http\Controllers\Api\BannerController::class, 'update']);
    Route::patch('/banners/{banner}/sort', [App\Http\Controllers\Api\BannerController::class, 'updateSort']);
    Route::delete('/banners/{banner}', [App\Http\Controllers\Api\BannerController::class, 'destroy']);

    // Videos (YouTube)
    Route::apiResource('videos', App\Http\Controllers\Api\VideoController::class)->names('api.videos');

    // Image upload for rich text editor
    Route::post('/upload-image', [App\Http\Controllers\Api\ImageUploadController::class, 'upload']);

    // AI Text routes
    Route::prefix('ai')->middleware('ai_enabled')->group(function () {
        Route::post('/chat', [AiTextController::class, 'chat']);
        Route::post('/translate', [AiTextController::class, 'translate']);

        // AI Image routes
        Route::post('/parse-image', [AiImageController::class, 'parseImage']);
        Route::post('/parse-images', [AiImageController::class, 'parseImages']);
        Route::post('/remove-background', [AiImageController::class, 'removeBackground']);
        Route::post('/generate-scene', [AiImageController::class, 'generateScene']);
    });

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::put('/{user}/toggle', [UserController::class, 'toggleStatus']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    // Usage routes
    Route::prefix('usage')->group(function () {
        Route::get('/current', [UsageController::class, 'current']);
        Route::get('/logs', [UsageController::class, 'logs']);
        Route::get('/statistics', [UsageController::class, 'statistics']);
    });

    // Admin Order routes
    Route::prefix('admin/orders')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Api\Admin\OrderController::class, 'show']);
        Route::patch('/{id}/status', [App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
    });

    // Knowledge Base routes
    Route::apiResource('knowledge-articles', App\Http\Controllers\Api\KnowledgeArticleController::class);
    Route::apiResource('knowledge-categories', App\Http\Controllers\Api\KnowledgeCategoryController::class);
});
