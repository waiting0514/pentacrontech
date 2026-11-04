<?php

namespace App\Providers;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Video;
use App\Observers\VideoObserver;
use App\Observers\KnowledgeArticleObserver;
use App\Observers\KnowledgeCategoryObserver;
use App\Observers\NewsCategoryObserver;
use App\Observers\NewsObserver;
use App\Observers\ProductCategoryObserver;
use App\Observers\ProductObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register model observers
        Product::observe(ProductObserver::class);
        ProductCategory::observe(ProductCategoryObserver::class);
        News::observe(NewsObserver::class);
        NewsCategory::observe(NewsCategoryObserver::class);
        KnowledgeArticle::observe(KnowledgeArticleObserver::class);
        KnowledgeCategory::observe(KnowledgeCategoryObserver::class);
        Video::observe(VideoObserver::class);

        // Define multilang route macro
        Route::macro('multilang', function ($uri, $action) {
            // 1. Default locale route (Clean URL)
            // e.g. /products
            $r1 = Route::get($uri, $action);
            
            // 2. Localized route (With prefix)
            // e.g. /{locale}/products
            // Remove leading slash to prevent double slash
            $cleanUri = ltrim($uri, '/');
            $r2 = Route::get("{locale}/{$cleanUri}", $action)
                ->where('locale', '[a-zA-Z]{2}(-[a-zA-Z]{2})?');
                
            return new class($r1, $r2) {
                protected $r1;
                protected $r2;

                public function __construct($r1, $r2) { 
                    $this->r1 = $r1; 
                    $this->r2 = $r2; 
                }

                public function name($name) {
                    $this->r1->name($name);
                    $this->r2->name($name . '.localized');
                    return $this;
                }

                public function where($name, $expression = null) {
                    // Apply where constraints to both routes
                    if (is_array($name)) {
                        $this->r1->where($name);
                        $this->r2->where($name);
                    } else {
                        $this->r1->where($name, $expression);
                        $this->r2->where($name, $expression);
                    }
                    return $this;
                }
            };
        });
    }
}