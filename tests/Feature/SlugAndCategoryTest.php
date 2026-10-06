<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SlugService;
use App\Services\AiCostControlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Mockery;

class SlugAndCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ProductCategory $defaultCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'product.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.update', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.delete', 'guard_name' => 'web']);
        Permission::create(['name' => 'category.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'category.delete', 'guard_name' => 'web']);

        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        // Create default category
        $this->defaultCategory = ProductCategory::create([
            'name' => ['zh' => '未分類', 'en' => 'Uncategorized'],
            'slug' => 'uncategorized',
            'is_system' => true,
            'is_active' => true,
        ]);

        // Create user
        $this->user = User::factory()->create(['is_active' => true]);
        $this->user->assignRole('super-admin');
    }

    // ==========================================
    // 8.1 測試：建立產品時自動生成 slug
    // ==========================================

    public function test_product_slug_auto_generated_on_create(): void
    {
        $product = Product::create([
            'name' => ['zh' => 'Test Product', 'en' => 'Test Product'],
            'description' => ['zh' => 'Description'],
            'price' => 100,
        ]);

        $this->assertNotNull($product->slug);
        $this->assertNotEmpty($product->slug);
    }

    public function test_product_uses_provided_slug(): void
    {
        $product = Product::create([
            'name' => ['zh' => 'Test Product'],
            'description' => ['zh' => 'Description'],
            'price' => 100,
            'slug' => 'my-custom-slug',
        ]);

        $this->assertEquals('my-custom-slug', $product->slug);
    }

    // ==========================================
    // 8.2 測試：建立分類時自動生成 slug
    // ==========================================

    public function test_category_slug_auto_generated_on_create(): void
    {
        $category = ProductCategory::create([
            'name' => ['zh' => '電子產品', 'en' => 'Electronics'],
            'is_active' => true,
        ]);

        $this->assertNotNull($category->slug);
        $this->assertNotEmpty($category->slug);
    }

    public function test_category_uses_provided_slug(): void
    {
        $category = ProductCategory::create([
            'name' => ['zh' => '電子產品'],
            'slug' => 'custom-category',
            'is_active' => true,
        ]);

        $this->assertEquals('custom-category', $category->slug);
    }

    // ==========================================
    // 8.3 測試：slug 衝突時自動附加數字後綴
    // ==========================================

    public function test_slug_conflict_auto_appends_suffix(): void
    {
        $product1 = Product::create([
            'name' => ['zh' => 'Test Product'],
            'description' => ['zh' => 'Description'],
            'price' => 100,
            'slug' => 'test-product',
        ]);

        $product2 = Product::create([
            'name' => ['zh' => 'Test Product'],
            'description' => ['zh' => 'Description'],
            'price' => 100,
            'slug' => 'test-product',
        ]);

        $this->assertEquals('test-product', $product1->slug);
        $this->assertEquals('test-product-1', $product2->slug);
    }

    // ==========================================
    // 8.4 測試：禁止刪除系統分類
    // ==========================================

    public function test_cannot_delete_system_category(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/product-categories/' . $this->defaultCategory->id);

        $response->assertStatus(403);
        $response->assertJson(['error_code' => 'SYSTEM_CATEGORY']);

        $this->assertDatabaseHas('product_categories', [
            'id' => $this->defaultCategory->id,
        ]);
    }

    public function test_system_category_is_system_method(): void
    {
        $this->assertTrue($this->defaultCategory->isSystemCategory());

        $regularCategory = ProductCategory::create([
            'name' => ['zh' => '一般分類'],
            'slug' => 'regular',
            'is_active' => true,
        ]);

        $this->assertFalse($regularCategory->isSystemCategory());
    }

    // ==========================================
    // 8.5 測試：刪除分類時產品重新指派至預設分類
    // ==========================================

    public function test_products_reassigned_to_default_category_on_delete(): void
    {
        $category = ProductCategory::create([
            'name' => ['zh' => '臨時分類'],
            'slug' => 'temp-category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => ['zh' => 'Test Product'],
            'description' => ['zh' => 'Description'],
            'price' => 100,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/product-categories/' . $category->id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.reassigned_products', 1);

        $product->refresh();
        $this->assertEquals($this->defaultCategory->id, $product->category_id);
    }

    public function test_delete_empty_category(): void
    {
        $category = ProductCategory::create([
            'name' => ['zh' => '空分類'],
            'slug' => 'empty-category',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/product-categories/' . $category->id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.reassigned_products', 0);

        $this->assertDatabaseMissing('product_categories', [
            'id' => $category->id,
        ]);
    }

    // ==========================================
    // 8.6 測試：AI 服務不可用時降級處理
    // ==========================================

    public function test_slug_fallback_when_ai_unavailable(): void
    {
        // Mock the SlugService to simulate AI failure
        $mockCostControl = Mockery::mock(AiCostControlService::class);
        $mockCostControl->shouldReceive('getSiteSetting')
            ->andReturn(SiteSetting::current());
        $mockCostControl->shouldReceive('checkBudget')
            ->andReturn(false); // Budget exceeded

        $slugService = new SlugService($mockCostControl);

        // Generate slug with Chinese name - should fallback to Str::slug
        $slug = $slugService->generateSlug('測試產品', 'products');

        // Should generate a slug even without AI
        $this->assertNotNull($slug);
        $this->assertNotEmpty($slug);
    }

    public function test_get_default_category_method(): void
    {
        $defaultCategory = ProductCategory::getDefaultCategory();

        $this->assertNotNull($defaultCategory);
        $this->assertTrue($defaultCategory->is_system);
        $this->assertEquals('uncategorized', $defaultCategory->slug);
    }

    public function test_system_category_protected_fields_on_update(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/product-categories/' . $this->defaultCategory->id, [
                'slug' => ['zh-TW' => 'hacked-slug'],
                'name' => ['zh-TW' => '已修改名稱', 'en' => 'Modified Name'],
            ]);

        $response->assertStatus(200);

        $this->defaultCategory->refresh();
        // Slug should remain unchanged
        $this->assertEquals('uncategorized', $this->defaultCategory->slug);
        // Name can be updated
        $this->assertEquals('Modified Name', $this->defaultCategory->getTranslation('name', 'en'));
        $this->assertEquals('已修改名稱', $this->defaultCategory->getTranslation('name', 'zh-TW'));
    }
}
