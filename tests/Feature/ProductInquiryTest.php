<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Laravel\Sanctum\Sanctum;

class ProductInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'product.create', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.view', 'guard_name' => 'web']);
        Permission::create(['name' => 'product.update', 'guard_name' => 'web']);
        
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    }

    public function test_can_create_product_with_inquiry_mode()
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/products', [
            'name' => ['en' => 'Test Product'],
            'description' => ['en' => 'Desc'],
            'price' => 100,
            'is_inquiry_only' => true
        ]);

        if ($response->status() !== 201) {
            $response->dump();
        }
        $response->assertStatus(201);
        $this->assertTrue($response->json('data.is_inquiry_only'));
        
        $product = Product::find($response->json('data.id'));
        $this->assertTrue($product->is_inquiry_only);
    }

    public function test_batch_action_updates_inquiry_mode()
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');

        $p1 = Product::create([
            'name' => ['zh' => 'P1'],
            'description' => ['zh' => 'D1'],
            'price' => 10,
            'is_inquiry_only' => false
        ]);
        
        $p2 = Product::create([
            'name' => ['zh' => 'P2'],
            'description' => ['zh' => 'D2'],
            'price' => 20,
            'is_inquiry_only' => false
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/products/batch-action', [
            'ids' => [$p1->id, $p2->id],
            'action' => 'set_inquiry_mode',
            'value' => true
        ]);

        $response->assertStatus(200);
        
        $this->assertTrue($p1->fresh()->is_inquiry_only);
        $this->assertTrue($p2->fresh()->is_inquiry_only);
    }

    public function test_can_update_site_default_inquiry_mode()
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super-admin');
        
        $setting = SiteSetting::current();
        $response = $this->actingAs($user, 'sanctum')->putJson('/api/settings', [
            'product_default_inquiry_mode' => true,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($setting->fresh()->product_default_inquiry_mode);
    }
}
