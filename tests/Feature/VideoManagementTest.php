<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VideoManagementTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'youtube_url' => self::URL,
            'title' => ['en' => '產品介紹影片', 'zh-TW' => 'Product Intro'],
        ], $override);
    }

    public function test_seeder_grants_video_permission_and_is_rerunnable(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertTrue(Role::findByName('manager')->hasPermissionTo('video.manage'));
        $this->assertTrue(Role::findByName('editor')->hasPermissionTo('video.manage'));
    }

    public function test_create_video_with_seo_fields(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->postJson('/api/videos', $this->payload([
            'description' => ['en' => '簡介'],
            'meta_title' => ['en' => 'SEO 標題'],
            'meta_description' => ['en' => 'SEO 描述'],
            'meta_keywords' => ['en' => '關鍵字一,關鍵字二'],
            'sort_order' => 3,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.youtube_id', 'dQw4w9WgXcQ')
            ->assertJsonPath('data.youtube_url', self::URL)
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.meta_title.en', 'SEO 標題')
            ->assertJsonPath('data.meta_keywords.en', '關鍵字一,關鍵字二')
            ->assertJsonPath('data.thumbnail_url', 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg');

        $this->assertDatabaseCount('videos', 1);
    }

    public function test_create_accepts_short_and_shorts_urls(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->postJson('/api/videos', $this->payload(['youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']))
            ->assertCreated()->assertJsonPath('data.youtube_id', 'dQw4w9WgXcQ');

        $this->postJson('/api/videos', $this->payload(['youtube_url' => 'https://www.youtube.com/shorts/abcdefghijk']))
            ->assertCreated()->assertJsonPath('data.youtube_id', 'abcdefghijk');
    }

    public function test_invalid_url_is_rejected_without_creating(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->postJson('/api/videos', $this->payload(['youtube_url' => 'https://example.com/video']))
            ->assertStatus(422)->assertJsonValidationErrors('youtube_url');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_missing_title_or_url_is_rejected(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->postJson('/api/videos', ['youtube_url' => self::URL, 'title' => ['zh-TW' => 'Only EN']])
            ->assertStatus(422)->assertJsonValidationErrors('title.en');

        $this->postJson('/api/videos', ['title' => ['en' => '標題']])
            ->assertStatus(422)->assertJsonValidationErrors('youtube_url');
    }

    public function test_duplicate_video_is_rejected(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->postJson('/api/videos', $this->payload())->assertCreated();
        $this->postJson('/api/videos', $this->payload(['youtube_url' => 'https://youtu.be/dQw4w9WgXcQ']))
            ->assertStatus(422)->assertJsonValidationErrors('youtube_url');

        $this->assertDatabaseCount('videos', 1);
    }

    public function test_slug_is_generated_normalized_and_unique(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $this->postJson('/api/videos', $this->payload(['title' => ['en' => 'Factory Tour']]))
            ->assertCreated()->assertJsonPath('data.slug', 'factory-tour');

        $this->postJson('/api/videos', $this->payload([
            'youtube_url' => 'https://youtu.be/abcdefghijk',
            'title' => ['en' => 'Factory Tour'],
        ]))->assertCreated()->assertJsonPath('data.slug', 'factory-tour-1');

        $this->postJson('/api/videos', $this->payload([
            'youtube_url' => 'https://youtu.be/ZYXWVUTSRQP',
            'slug' => 'My Custom Slug!',
        ]))->assertCreated()->assertJsonPath('data.slug', 'my-custom-slug');
    }

    public function test_chinese_title_gets_a_non_empty_slug(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));

        $slug = $this->postJson('/api/videos', $this->payload())->assertCreated()->json('data.slug');

        $this->assertMatchesRegularExpression('/^[a-z0-9\-]+$/', $slug);
    }

    public function test_update_title_url_and_status(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));
        $id = $this->postJson('/api/videos', $this->payload())->json('data.id');

        $this->putJson("/api/videos/{$id}", [
            'title' => ['en' => '新標題'],
            'youtube_url' => 'https://youtu.be/abcdefghijk',
            'is_active' => false,
            'meta_title' => ['en' => '新 SEO'],
        ])
            ->assertOk()
            ->assertJsonPath('data.title.en', '新標題')
            ->assertJsonPath('data.youtube_id', 'abcdefghijk')
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.meta_title.en', '新 SEO');
    }

    public function test_update_with_same_url_is_not_a_duplicate(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));
        $id = $this->postJson('/api/videos', $this->payload())->json('data.id');

        $this->putJson("/api/videos/{$id}", ['youtube_url' => self::URL, 'title' => ['en' => '改名']])
            ->assertOk()->assertJsonPath('data.title.en', '改名');
    }

    public function test_update_with_invalid_url_keeps_original(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));
        $id = $this->postJson('/api/videos', $this->payload())->json('data.id');

        $this->putJson("/api/videos/{$id}", ['youtube_url' => 'not a url'])
            ->assertStatus(422)->assertJsonValidationErrors('youtube_url');

        $this->assertSame('dQw4w9WgXcQ', Video::find($id)->youtube_id);
    }

    public function test_update_keeps_slug_when_title_changes(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));
        $created = $this->postJson('/api/videos', $this->payload(['title' => ['en' => 'Old Name']]))->json('data');

        $this->putJson("/api/videos/{$created['id']}", ['title' => ['en' => 'New Name']])
            ->assertOk()->assertJsonPath('data.slug', $created['slug']);
    }

    public function test_delete_video(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));
        $id = $this->postJson('/api/videos', $this->payload())->json('data.id');

        $this->deleteJson("/api/videos/{$id}")->assertOk();
        $this->assertDatabaseMissing('videos', ['id' => $id]);
    }

    public function test_list_is_ordered_by_sort_order(): void
    {
        Sanctum::actingAs($this->userWithRole('editor'));
        $this->postJson('/api/videos', $this->payload(['title' => ['en' => 'B'], 'sort_order' => 2]));
        $this->postJson('/api/videos', $this->payload(['youtube_url' => 'https://youtu.be/abcdefghijk', 'title' => ['en' => 'A'], 'sort_order' => 1]));

        $titles = collect($this->getJson('/api/videos')->assertOk()->json('data'))->pluck('title.en')->all();

        $this->assertSame(['A', 'B'], $titles);
    }

    public function test_user_without_permission_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(Role::firstOrCreate(['name' => 'editor']));
        Role::findByName('editor')->revokePermissionTo('video.manage');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Sanctum::actingAs($user->fresh());

        $this->getJson('/api/videos')->assertForbidden();
        $this->postJson('/api/videos', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('videos', 0);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/api/videos')->assertUnauthorized();
        $this->postJson('/api/videos', $this->payload())->assertUnauthorized();
    }
}
