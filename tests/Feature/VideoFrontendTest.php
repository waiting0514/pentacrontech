<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VideoFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SiteSetting::create(['name' => ['zh-TW' => '測試網站', 'en' => 'Test Site'], 'yearly_budget_twd' => 1000]);
    }

    private function video(array $attrs = []): Video
    {
        static $n = 0;
        $n++;

        return Video::create(array_merge([
            'title' => ['zh-TW' => "Video {$n}", 'en' => "Video {$n}"],
            'slug' => "video-{$n}",
            'youtube_id' => str_pad((string) $n, 11, 'a'),
        ], $attrs));
    }

    public function test_list_shows_only_active_videos_in_order(): void
    {
        $this->video(['title' => ['zh-TW' => 'Second', 'en' => 'Second'], 'sort_order' => 2]);
        $this->video(['title' => ['zh-TW' => 'First', 'en' => 'First'], 'sort_order' => 1]);
        $this->video(['title' => ['zh-TW' => 'Hidden', 'en' => 'Hidden'], 'is_active' => false]);

        $this->get('/videos')
            ->assertOk()
            ->assertSeeInOrder(['First', 'Second'])
            ->assertDontSee('Hidden');
    }

    public function test_list_empty_state_does_not_error(): void
    {
        $this->get('/videos')->assertOk();
    }

    public function test_list_paginates_twelve_per_page(): void
    {
        foreach (range(1, 13) as $i) {
            $this->video(['title' => ['zh-TW' => sprintf('Item%02d', $i), 'en' => sprintf('Item%02d', $i)], 'sort_order' => $i]);
        }

        $this->get('/videos')->assertOk()->assertSee('Item12')->assertDontSee('Item13');
        $this->get('/videos?page=2')->assertOk()->assertSee('Item13');
    }

    public function test_detail_embeds_player_without_autoplay_and_has_video_object(): void
    {
        $video = $this->video(['youtube_id' => 'dQw4w9WgXcQ', 'description' => ['zh-TW' => '簡介文字', 'en' => 'Intro text']]);

        $html = $this->get("/videos/{$video->id}-{$video->slug}")->assertOk()->getContent();

        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $html);
        $this->assertStringNotContainsString('autoplay', strtolower($html));
        $this->assertStringContainsString('"@type":"VideoObject"', str_replace(' ', '', $html));
        $this->assertStringContainsString('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $html);
    }

    public function test_detail_uses_custom_seo_fields(): void
    {
        $video = $this->video([
            'meta_title' => ['zh-TW' => 'Custom SEO Title', 'en' => 'Custom SEO Title'],
            'meta_description' => ['zh-TW' => 'Custom SEO description', 'en' => 'Custom SEO description'],
            'meta_keywords' => ['zh-TW' => 'kw-one,kw-two', 'en' => 'kw-one,kw-two'],
        ]);

        $this->get("/videos/{$video->id}-{$video->slug}")
            ->assertOk()
            ->assertSee('<title>Custom SEO Title', false)
            ->assertSee('<meta name="description" content="Custom SEO description">', false)
            ->assertSee('<meta name="keywords" content="kw-one,kw-two">', false);
    }

    public function test_detail_falls_back_to_title_and_description(): void
    {
        $video = $this->video([
            'title' => ['zh-TW' => 'Fallback Title', 'en' => 'Fallback Title'],
            'description' => ['zh-TW' => 'Fallback description body', 'en' => 'Fallback description body'],
        ]);

        $this->get("/videos/{$video->id}-{$video->slug}")
            ->assertOk()
            ->assertSee('<title>Fallback Title', false)
            ->assertSee('<meta name="description" content="Fallback description body">', false);
    }

    public function test_wrong_or_missing_slug_redirects_permanently(): void
    {
        $video = $this->video();

        $this->get("/videos/{$video->id}-wrong-slug")->assertStatus(301)->assertRedirect($video->url);
        $this->get("/videos/{$video->id}")->assertStatus(301)->assertRedirect($video->url);
    }

    public function test_inactive_or_missing_video_is_404(): void
    {
        $inactive = $this->video(['is_active' => false]);

        $this->get("/videos/{$inactive->id}-{$inactive->slug}")->assertNotFound();
        $this->get("/videos/{$inactive->id}")->assertNotFound();
        $this->get('/videos/99999-nothing')->assertNotFound();
    }

    public function test_footer_links_to_video_list_when_not_hidden(): void
    {
        config(['frontend.hidden_nav' => []]);

        $this->get('/about')->assertOk()->assertSee(url('/videos'), false);
    }

    public function test_video_and_product_links_are_hidden_from_navigation_by_default(): void
    {
        $this->assertSame(['products', 'videos'], config('frontend.hidden_nav'));

        $html = $this->get('/about')->assertOk()->getContent();

        // 只檢查選單區塊（header 主選單、手機選單、footer 欄位），內文可能有其他連結
        preg_match('#<nav class="ix-nav".*?</nav>#s', $html, $headerNav);
        $this->assertNotEmpty($headerNav);
        $this->assertStringNotContainsString(url('/products'), $headerNav[0]);
        $this->assertStringNotContainsString(url('/videos'), $headerNav[0]);
        $this->assertStringContainsString('#applications', $headerNav[0]);
        $this->assertStringContainsString('>'.__('frontend.nav.applications').'<', $headerNav[0]);

        preg_match('#<footer.*</footer>#s', $html, $footer);
        $this->assertStringNotContainsString(url('/videos'), $footer[0]);
        $this->assertStringNotContainsString(url('/products'), $footer[0]);
    }

    public function test_sitemap_includes_active_videos_only(): void
    {
        $path = public_path('sitemap.xml');
        $backup = file_exists($path) ? file_get_contents($path) : null;

        try {
            $active = $this->video();
            $inactive = $this->video(['is_active' => false]);

            Artisan::call('seo:generate-sitemap');
            $xml = file_get_contents($path);

            $this->assertStringContainsString("/videos/{$active->id}-{$active->slug}", $xml);
            $this->assertStringContainsString('/videos<', $xml);
            $this->assertStringNotContainsString("/videos/{$inactive->id}-{$inactive->slug}", $xml);
        } finally {
            $backup === null ? @unlink($path) : file_put_contents($path, $backup);
        }
    }
}
