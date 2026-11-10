<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KnownIssuesFixTest extends TestCase
{
    use RefreshDatabase;

    private SiteSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        foreach (['system.manage', 'product.view', 'product.update', 'product.delete', 'order.view'] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'customer', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web'])
            ->syncPermissions(['product.view', 'product.update']);

        $this->setting = SiteSetting::create([
            'name' => ['en' => 'Test Site', 'zh-TW' => '測試網站'],
            'yearly_budget_twd' => 1000,
        ]);
    }

    private function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true], $attrs));
        $user->assignRole($role);

        return $user;
    }

    public function test_editor_cannot_update_site_settings(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->putJson('/api/settings', ['yearly_budget_twd' => 999999])
            ->assertForbidden();
        $this->assertSame('1000.00', $this->setting->fresh()->yearly_budget_twd);
    }

    public function test_editor_cannot_read_usage_or_orders(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->getJson('/api/usage/statistics')->assertForbidden();
        $this->getJson('/api/admin/orders')->assertForbidden();
    }

    public function test_editor_cannot_delete_product_category(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $category = \App\Models\ProductCategory::create(['name' => ['zh-TW' => '分類']]);

        $this->deleteJson("/api/product-categories/{$category->id}")->assertForbidden();
    }

    public function test_captcha_secrets_are_encrypted_and_not_exposed(): void
    {
        Sanctum::actingAs($this->userWithRole('super-admin'));

        $this->putJson('/api/settings', [
            'recaptcha_secret_key' => 'recaptcha-secret',
            'turnstile_secret_key' => 'turnstile-secret',
        ])->assertOk();

        $raw = \DB::table('site_settings')->first();
        $this->assertNotSame('recaptcha-secret', $raw->recaptcha_secret_key);
        $this->assertNotSame('turnstile-secret', $raw->turnstile_secret_key);
        $this->assertSame('recaptcha-secret', $this->setting->fresh()->recaptcha_secret_key);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonMissingPath('data.recaptcha_secret_key')
            ->assertJsonMissingPath('data.turnstile_secret_key')
            ->assertJsonPath('data.has_recaptcha_secret_key', true)
            ->assertJsonPath('data.has_turnstile_secret_key', true);

        // 留空不覆蓋既有金鑰
        $this->putJson('/api/settings', ['recaptcha_secret_key' => ''])->assertOk();
        $this->assertSame('recaptcha-secret', $this->setting->fresh()->recaptcha_secret_key);
    }

    public function test_usage_statistics_total_cost_is_never_null(): void
    {
        Sanctum::actingAs($this->userWithRole('super-admin'));

        \DB::table('ai_usage_logs')->insert([
            'user_id' => User::factory()->create()->id,
            'provider' => 'openai',
            'feature' => 'chat',
            'status' => 'success',
            'cost_usd' => 0,
            'display_cost_twd' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/usage/statistics')->assertOk();

        // MySQL 的 decimal 以字串回傳（'0.00'），前端 parseFloat 後為 0；重點是不能是 null
        $total = $response->json('data.statistics.0.total_cost_twd');
        $this->assertNotNull($total);
        $this->assertEquals(0, $total);
    }

    public function test_maintenance_mode_blocks_all_frontend_pages(): void
    {
        $this->setting->update(['maintenance_mode' => true]);

        $this->get('/products')->assertStatus(503);
        $this->get('/about')->assertStatus(503);
    }

    public function test_inactive_customer_cannot_login(): void
    {
        $this->userWithRole('customer', ['username' => 'blocked', 'is_active' => false, 'password' => 'secret123']);

        $this->post('/login', ['username' => 'blocked', 'password' => 'secret123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_page_shows_site_name(): void
    {
        $this->get('/login')->assertOk()->assertSee(\App\Models\SiteSetting::current()->name);
    }

    public function test_rag_search_matches_zh_tw_name(): void
    {
        $category = \App\Models\ProductCategory::create(['name' => ['zh-TW' => '分類']]);
        \App\Models\Product::create([
            'name' => ['zh-TW' => '不鏽鋼水杯', 'en' => 'Steel cup'],
            'price' => 100,
            'product_category_id' => $category->id,
        ]);

        $this->assertCount(1, app(\App\Services\RagService::class)->searchProducts('水杯'));
    }
}
