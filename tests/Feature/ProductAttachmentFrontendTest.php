<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttachment;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductAttachmentFrontendTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SiteSetting::create(['name' => ['zh-TW' => '測試網站', 'en' => 'Test Site'], 'yearly_budget_twd' => 1000]);
        Language::create(['code' => 'zh-TW', 'name' => '繁體中文', 'is_default' => true, 'is_active' => true]);
        Language::create(['code' => 'en', 'name' => 'English', 'is_default' => false, 'is_active' => true]);

        $this->product = Product::create(['name' => ['zh-TW' => '機器', 'en' => 'Machine'], 'price' => 100]);
    }

    private function attachment(array $title, array $files, int $sort = 0): ProductAttachment
    {
        $attachment = $this->product->attachments()->create(['title' => $title, 'sort_order' => $sort]);
        foreach ($files as $locale => $name) {
            $attachment->files()->create([
                'locale' => $locale,
                'path' => "product-attachments/{$this->product->id}/{$locale}-{$name}",
                'original_name' => $name,
                'size' => 2411520,
            ]);
        }

        return $attachment;
    }

    /**
     * slug 只存於建立時的語系；請求會切換 app locale，讀取 slug 時要固定取已儲存的那一筆，
     * 否則第二次請求會拿到空字串而 404。
     */
    private function productSlug(): string
    {
        return (string) collect($this->product->getTranslations('slug'))->first();
    }

    private function page(string $locale): \Illuminate\Testing\TestResponse
    {
        $prefix = $locale === 'zh-TW' ? '' : "/{$locale}";

        return $this->get("{$prefix}/products/{$this->product->id}-".$this->productSlug());
    }

    public function test_shows_file_and_title_for_current_locale(): void
    {
        $this->attachment(['zh-TW' => '產品型錄', 'en' => 'Catalog'], ['zh-TW' => 'zh.pdf', 'en' => 'en.pdf']);

        $this->page('en')->assertOk()
            ->assertSee('Catalog')
            ->assertSee('en-en.pdf', false)
            ->assertDontSee('zh-TW-zh.pdf', false)
            ->assertSee('2.3 MB');

        $this->page('zh-TW')->assertOk()
            ->assertSee('產品型錄')
            ->assertSee('zh-TW-zh.pdf', false)
            ->assertDontSee('en-en.pdf', false);
    }

    public function test_falls_back_to_default_locale_file_with_language_tag(): void
    {
        $this->attachment(['zh-TW' => '安裝手冊'], ['zh-TW' => 'manual.pdf']);

        $html = $this->page('en')->assertOk()
            ->assertSee('zh-TW-manual.pdf', false)
            ->getContent();

        // Title falls back to the default-language title, with a language tag.
        $this->assertStringContainsString('安裝手冊', $html);
        $this->assertMatchesRegularExpression('/uppercase[^>]*>\s*zh-TW\s*</', $html);
    }

    public function test_no_language_tag_when_file_matches_locale(): void
    {
        $this->attachment(['zh-TW' => '型錄', 'en' => 'Catalog'], ['zh-TW' => 'a.pdf', 'en' => 'b.pdf']);

        $this->assertDoesNotMatchRegularExpression('/uppercase[^>]*>\s*en\s*</', $this->page('en')->getContent());
    }

    public function test_no_downloads_section_without_attachments(): void
    {
        $this->page('en')->assertOk()->assertDontSee('id="downloads"', false);
    }

    public function test_attachments_are_ordered(): void
    {
        $this->attachment(['zh-TW' => 'Second', 'en' => 'Second'], ['en' => 's.pdf'], 2);
        $this->attachment(['zh-TW' => 'First', 'en' => 'First'], ['en' => 'f.pdf'], 1);

        $this->page('en')->assertOk()->assertSeeInOrder(['First', 'Second']);
    }

    public function test_download_link_is_safe_and_uses_original_name(): void
    {
        $this->attachment(['zh-TW' => '型錄'], ['zh-TW' => 'Spec Sheet.pdf']);

        $this->page('zh-TW')->assertOk()
            ->assertSee('download="Spec Sheet.pdf"', false)
            ->assertSee('rel="noopener"', false);
    }
}
