<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\KnowledgeController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\InquiryController;
use App\Http\Controllers\Frontend\Auth\RegisterController;
use App\Http\Controllers\Frontend\Auth\LoginController;
use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\OrderHistoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'maintenance'])->group(function () {

    // Homepage
    // Note: Homepage regex handling is special if using {locale?}/ pattern,
    // but with multilang macro, we define '/' and '{locale}/'.
    Route::multilang('/', [HomeController::class, 'index'])->name('home');

    // Products
    Route::multilang('/products', [ProductController::class, 'index'])->name('products.index');
    Route::multilang('/products/{id}-{slug}', [ProductController::class, 'show'])
        ->where(['id' => '[0-9]+', 'slug' => '[a-z0-9\-]+'])
        ->name('products.show');
    Route::multilang('/products/{id}', [ProductController::class, 'redirectToSlug'])
        ->where('id', '[0-9]+'); // No name needed for redirect route usually, but multilang supports it

    // Product Categories
    Route::multilang('/category/{slug}', [ProductController::class, 'category'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('category.show');

    // News
    Route::multilang('/news', [NewsController::class, 'index'])->name('news.index');
    Route::multilang('/news/{id}-{slug}', [NewsController::class, 'show'])
        ->where(['id' => '[0-9]+', 'slug' => '[a-z0-9\-]+'])
        ->name('news.show');
    Route::multilang('/news/{id}', [NewsController::class, 'redirectToSlug'])
        ->where('id', '[0-9]+');

    // Knowledge Base
    Route::multilang('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
    Route::multilang('/knowledge/{categorySlug}/{id}-{slug}', [KnowledgeController::class, 'show'])
        ->where(['categorySlug' => '[a-z0-9\-]+', 'id' => '[0-9]+', 'slug' => '[a-z0-9\-]+'])
        ->name('knowledge.show');
    Route::multilang('/knowledge/{categorySlug}/{id}', [KnowledgeController::class, 'redirectToSlug'])
        ->where(['categorySlug' => '[a-z0-9\-]+', 'id' => '[0-9]+']);
    Route::multilang('/knowledge/{categorySlug}', [KnowledgeController::class, 'category'])
        ->where('categorySlug', '[a-z0-9\-]+')
        ->name('knowledge.category');

    // About
    Route::multilang('/about', [AboutController::class, 'index'])->name('about');

    // Contact
    Route::multilang('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

    // Frontend Auth (guest only)
    Route::middleware('guest')->group(function () {
        Route::multilang('/register', [RegisterController::class, 'showForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'store']);
        Route::multilang('/login', [LoginController::class, 'showForm'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);
    });

    // Frontend Auth (logged in)
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

    // Account (auth required)
    Route::middleware('auth')->group(function () {
        Route::get('/account', [AccountController::class, 'index'])->name('account.show');
        Route::post('/account', [AccountController::class, 'update'])->name('account.update');
        Route::get('/account/orders', [OrderHistoryController::class, 'index'])->name('account.orders.index');
        Route::get('/account/orders/{order}', [OrderHistoryController::class, 'show'])->name('account.orders.show');
    });

    // Inquiry Cart
    Route::multilang('/inquiry', [InquiryController::class, 'index'])->name('inquiry.index');
    Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');
    Route::multilang('/inquiry/success', [InquiryController::class, 'success'])->name('inquiry.success');
});

// ============================================
// SEO Routes (No Locale Prefix)
// ============================================

// Robots.txt
Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /admin\n";
    $content .= "Disallow: /api\n\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";
    
    return response($content, 200, ['Content-Type' => 'text/plain']);
});

// ============================================
// Admin/Dashboard Routes (SPA)
// ============================================

// Admin SPA
Route::get('/admin/{any?}', function () {
    return view('dashboard');
})->where('any', '.*')->name('admin');