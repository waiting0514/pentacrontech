<?php

namespace Tests\Feature;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MultilangRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        
        // 確保 Macro 生效 (AppServiceProvider boot)
        // 測試環境通常會自動載入
    }

    public function test_default_language_urls_have_no_prefix()
    {
        Language::create(['code' => 'zh-TW', 'name' => '繁體中文', 'is_default' => true, 'is_active' => true]);
        Language::create(['code' => 'en', 'name' => 'English', 'is_default' => false, 'is_active' => true]);

        // 訪問 /products (無前綴)
        $response = $this->get('/products');
        $response->assertStatus(200);
        $this->assertEquals('zh-TW', app()->getLocale());
    }

    public function test_non_default_language_urls_have_prefix()
    {
        Language::create(['code' => 'zh-TW', 'name' => '繁體中文', 'is_default' => true, 'is_active' => true]);
        Language::create(['code' => 'en', 'name' => 'English', 'is_default' => false, 'is_active' => true]);

        // 訪問 /en/products
        $response = $this->get('/en/products');
        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_accessing_default_language_with_prefix_redirects()
    {
        Language::create(['code' => 'zh-TW', 'name' => '繁體中文', 'is_default' => true, 'is_active' => true]);
        
        // 訪問 /zh-TW/products -> 301 Redirect to /products
        $response = $this->get('/zh-TW/products');
        $response->assertStatus(301);
        $response->assertRedirect('/products');
    }

    public function test_lroute_helper_generates_correct_urls()
    {
        Language::create(['code' => 'zh-TW', 'name' => '繁體中文', 'is_default' => true, 'is_active' => true]);
        Language::create(['code' => 'en', 'name' => 'English', 'is_default' => false, 'is_active' => true]);

        // Case 1: Current context is default locale
        $this->get('/products'); 
        
        // lroute should return clean URL
        $urlDefault = lroute('products.index');
        $this->assertStringNotContainsString('/zh-TW/products', $urlDefault);
        $this->assertStringEndsWith('/products', $urlDefault);

        // Case 2: Current context is non-default locale
        $this->get('/en/products');
        
        // lroute should return localized URL
        $urlEn = lroute('products.index');
        $this->assertStringContainsString('/en/products', $urlEn);
    }
    
    public function test_homepage_routing()
    {
        Language::create(['code' => 'zh-TW', 'name' => '繁體中文', 'is_default' => true, 'is_active' => true]);
        Language::create(['code' => 'en', 'name' => 'English', 'is_default' => false, 'is_active' => true]);

        // / -> zh-TW
        $this->get('/')->assertStatus(200);
        $this->assertEquals('zh-TW', app()->getLocale());
        
        // /en -> en
        $this->get('/en')->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());
        
        // /zh-TW -> redirect /
        $this->get('/zh-TW')->assertStatus(301)->assertRedirect('/');
    }
}
