<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAttachment;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->product = Product::create(['name' => ['en' => '產品', 'zh-TW' => 'Product'], 'price' => 100]);
    }

    private function actingEditor(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('editor');
        Sanctum::actingAs($user);

        return $user;
    }

    private function pdf(string $name = 'catalog.pdf', int $extraBytes = 0): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n".str_repeat('a', $extraBytes)
        );
    }

    private function url(string $suffix = '', ?Product $product = null): string
    {
        return '/api/products/'.($product ?? $this->product)->id.'/attachments'.$suffix;
    }

    private function create(array $override = [])
    {
        return $this->post($this->url(), array_merge([
            'title' => ['en' => '產品型錄', 'zh-TW' => 'Catalog'],
            'files' => ['en' => $this->pdf('型錄.pdf'), 'zh-TW' => $this->pdf('catalog.pdf')],
        ], $override), ['Accept' => 'application/json']);
    }

    public function test_create_attachment_with_zh_and_en_files(): void
    {
        $this->actingEditor();

        $res = $this->create()->assertCreated();

        $res->assertJsonPath('data.title.en', '產品型錄')
            ->assertJsonPath('data.title.zh-TW', 'Catalog')
            ->assertJsonCount(2, 'data.files');

        $files = collect($res->json('data.files'))->keyBy('locale');
        $this->assertSame('型錄.pdf', $files['en']['original_name']);
        $this->assertSame('catalog.pdf', $files['zh-TW']['original_name']);
        $this->assertGreaterThan(0, $files['zh-TW']['size']);

        $this->assertDatabaseCount('product_attachment_files', 2);
        $this->assertCount(2, Storage::disk('public')->allFiles('product-attachments'));
    }

    public function test_create_with_single_locale_is_allowed(): void
    {
        $this->actingEditor();

        $this->create(['files' => ['zh-TW' => $this->pdf()]])
            ->assertCreated()->assertJsonCount(1, 'data.files');
    }

    public function test_files_are_stored_under_random_names(): void
    {
        $this->actingEditor();

        $this->create(['files' => ['en' => $this->pdf('../../evil.pdf')]])->assertCreated();

        $path = \App\Models\ProductAttachmentFile::first()->path;
        $this->assertStringStartsWith('product-attachments/'.$this->product->id.'/', $path);
        $this->assertStringNotContainsString('evil', $path);
        $this->assertStringEndsWith('.pdf', $path);
    }

    public function test_requires_at_least_one_file(): void
    {
        $this->actingEditor();

        $this->post($this->url(), ['title' => ['en' => '標題']], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('files');

        $this->assertDatabaseCount('product_attachments', 0);
    }

    public function test_requires_default_locale_title(): void
    {
        $this->actingEditor();

        $this->create(['title' => ['zh-TW' => 'Only EN']])
            ->assertStatus(422)->assertJsonValidationErrors('title.en');
    }

    public function test_rejects_non_pdf_and_disguised_files(): void
    {
        $this->actingEditor();

        $this->create(['files' => ['en' => UploadedFile::fake()->createWithContent('photo.jpg', '����JFIF')]])
            ->assertStatus(422)->assertJsonValidationErrors('files.en');

        $this->assertDatabaseCount('product_attachments', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    /**
     * Fake uploads report their MIME from the extension, so use real temp files
     * to exercise server-side content detection.
     */
    private function realFile(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'att');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    public function test_content_detection_rejects_non_pdf_renamed_to_pdf(): void
    {
        $this->actingEditor();

        $this->create(['files' => ['en' => $this->realFile('evil.pdf', '<?php echo "x"; ?>')]])
            ->assertStatus(422)->assertJsonValidationErrors('files.en');

        $this->create(['files' => ['en' => $this->realFile('image.pdf', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='))]])
            ->assertStatus(422)->assertJsonValidationErrors('files.en');

        $this->assertDatabaseCount('product_attachments', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_content_detection_accepts_real_pdf(): void
    {
        $this->actingEditor();

        $pdf = '%PDF-1.4
1 0 obj<</Type/Catalog>>endobj
trailer<</Root 1 0 R>>
%%EOF
';
        $this->create(['files' => ['en' => $this->realFile('real.pdf', $pdf)]])->assertCreated();
    }

    public function test_rejects_unknown_locale(): void
    {
        $this->actingEditor();

        $this->create(['files' => ['xx' => $this->pdf()]])
            ->assertStatus(422)->assertJsonValidationErrors('files');
    }

    public function test_rejects_file_over_default_limit_with_message(): void
    {
        $this->actingEditor();

        $big = $this->pdf('big.pdf', 10 * 1024 * 1024);
        $res = $this->create(['files' => ['en' => $big]])
            ->assertStatus(422)->assertJsonValidationErrors('files.en');

        $this->assertStringContainsString('10 MB', $res->json('errors')['files.en'][0]);
    }

    public function test_size_limit_is_configurable(): void
    {
        $this->actingEditor();
        $big = $this->pdf('big.pdf', 2 * 1024 * 1024);

        config(['uploads.product_attachment_max_kb' => 1024]);
        $this->create(['files' => ['en' => $big]])->assertStatus(422);

        config(['uploads.product_attachment_max_kb' => 20480]);
        $this->create(['files' => ['en' => $this->pdf('big.pdf', 2 * 1024 * 1024)]])->assertCreated();
    }

    public function test_rejects_when_product_reached_attachment_limit(): void
    {
        $this->actingEditor();
        config(['uploads.product_attachment_max_per_product' => 2]);

        $this->create()->assertCreated();
        $this->create()->assertCreated();
        $this->create()->assertStatus(422)->assertJsonValidationErrors('attachment');

        $this->assertDatabaseCount('product_attachments', 2);
    }

    public function test_update_title_and_replace_one_locale_deletes_old_file(): void
    {
        $this->actingEditor();
        $created = $this->create()->json('data');
        $oldEn = \App\Models\ProductAttachmentFile::where('locale', 'zh-TW')->first()->path;

        $this->post($this->url('/'.$created['id']), [
            'title' => ['en' => '新標題', 'zh-TW' => 'New title'],
            'files' => ['zh-TW' => $this->pdf('new-en.pdf')],
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.title.en', '新標題')
            ->assertJsonCount(2, 'data.files');

        $newEn = \App\Models\ProductAttachmentFile::where('locale', 'zh-TW')->first();
        $this->assertSame('new-en.pdf', $newEn->original_name);
        $this->assertNotSame($oldEn, $newEn->path);
        Storage::disk('public')->assertMissing($oldEn);
        Storage::disk('public')->assertExists($newEn->path);
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_update_can_add_a_locale(): void
    {
        $this->actingEditor();
        $id = $this->create(['files' => ['en' => $this->pdf()]])->json('data.id');

        $this->post($this->url('/'.$id), ['files' => ['zh-TW' => $this->pdf('en.pdf')]], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonCount(2, 'data.files');
    }

    public function test_remove_one_locale_file_but_not_the_last(): void
    {
        $this->actingEditor();
        $id = $this->create()->json('data.id');

        $this->deleteJson($this->url("/{$id}/files/zh-TW"))->assertOk()->assertJsonCount(1, 'data.files');
        $this->assertCount(1, Storage::disk('public')->allFiles());

        $this->deleteJson($this->url("/{$id}/files/en"))
            ->assertStatus(422)->assertJsonValidationErrors('file');
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_delete_attachment_removes_files(): void
    {
        $this->actingEditor();
        $id = $this->create()->json('data.id');

        $this->deleteJson($this->url("/{$id}"))->assertOk();

        $this->assertDatabaseCount('product_attachments', 0);
        $this->assertDatabaseCount('product_attachment_files', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleting_product_removes_all_physical_files(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin);
        $this->create()->assertCreated();
        $this->create()->assertCreated();
        $this->assertCount(4, Storage::disk('public')->allFiles());

        $this->deleteJson('/api/products/'.$this->product->id)->assertOk();

        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('product_attachments', 0);
    }

    public function test_attachment_of_another_product_is_404(): void
    {
        $this->actingEditor();
        $other = Product::create(['name' => ['en' => '另一個'], 'price' => 100]);
        $id = $this->create()->json('data.id');

        $this->deleteJson($this->url("/{$id}", $other))->assertNotFound();
        $this->post($this->url("/{$id}", $other), ['sort_order' => 5], ['Accept' => 'application/json'])->assertNotFound();
        $this->deleteJson($this->url("/{$id}/files/zh-TW", $other))->assertNotFound();

        $this->assertDatabaseHas('product_attachments', ['id' => $id]);
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_list_is_ordered(): void
    {
        $this->actingEditor();
        $this->create(['title' => ['en' => 'B'], 'sort_order' => 2]);
        $this->create(['title' => ['en' => 'A'], 'sort_order' => 1]);

        $titles = collect($this->getJson($this->url())->assertOk()->json('data'))->pluck('title.en')->all();

        $this->assertSame(['A', 'B'], $titles);
    }

    public function test_user_without_update_permission_gets_403(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('editor');
        Role::findByName('editor')->revokePermissionTo('product.update');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Sanctum::actingAs($user->fresh());

        $this->getJson($this->url())->assertOk();
        $this->create()->assertForbidden();
        $this->assertDatabaseCount('product_attachments', 0);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson($this->url())->assertUnauthorized();
        $this->postJson($this->url())->assertUnauthorized();
    }

    public function test_file_resolution_falls_back_to_default_then_any(): void
    {
        $this->actingEditor();
        $id = $this->create()->json('data.id');
        $attachment = ProductAttachment::with('files')->find($id);

        $this->assertSame('zh-TW', $attachment->fileFor('zh-TW')->locale);
        $this->assertSame('en', $attachment->fileFor('ja')->locale); // falls back to default

        $attachment->files()->where('locale', 'en')->first()->delete();
        $attachment = ProductAttachment::with('files')->find($id);
        $this->assertSame('zh-TW', $attachment->fileFor('ja')->locale); // default missing -> any
    }
}
