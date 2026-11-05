<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductAttachmentResource;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttachment;
use App\Models\ProductAttachmentFile;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductAttachmentController extends Controller
{
    use ApiResponse;

    public function index(Product $product): JsonResponse
    {
        $this->authorize('product.view');

        return $this->successResponse(
            ProductAttachmentResource::collection($product->attachments()->with('files')->get())
        );
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $this->authorize('product.update');

        $max = (int) config('uploads.product_attachment_max_per_product');
        if ($product->attachments()->count() >= $max) {
            throw ValidationException::withMessages([
                'attachment' => "每個產品最多只能有 {$max} 份附件",
            ]);
        }

        $default = Language::defaultCode();
        $validated = $request->validate(array_merge($this->rules(), [
            'title' => ['required', 'array'],
            "title.{$default}" => ['required', 'string', 'max:255'],
            'files' => ['required', 'array', 'min:1'],
        ]), $this->messages());

        $this->assertKnownLocales($validated['files'] ?? []);

        $attachment = DB::transaction(function () use ($product, $validated, $request) {
            $attachment = $product->attachments()->create([
                'title' => array_filter($validated['title'], fn ($v) => filled($v)),
                'sort_order' => $validated['sort_order'] ?? $product->attachments()->count(),
            ]);

            foreach ($request->file('files', []) as $locale => $file) {
                $this->saveFile($attachment, $locale, $file);
            }

            return $attachment;
        });

        return $this->successResponse(
            new ProductAttachmentResource($attachment->load('files')),
            'Attachment created',
            201
        );
    }

    public function update(Request $request, Product $product, ProductAttachment $attachment): JsonResponse
    {
        $this->authorize('product.update');
        $this->ensureBelongs($product, $attachment);

        $default = Language::defaultCode();
        $validated = $request->validate(array_merge($this->rules(), [
            'title' => ['sometimes', 'array'],
            "title.{$default}" => ['required_with:title', 'string', 'max:255'],
            'files' => ['nullable', 'array'],
        ]), $this->messages());

        $this->assertKnownLocales($validated['files'] ?? []);

        DB::transaction(function () use ($attachment, $validated, $request) {
            if (isset($validated['title'])) {
                // Spatie merges per-locale on assignment; clear first so blanked locales are removed.
                $attachment->forgetTranslations('title')
                    ->setTranslations('title', array_filter($validated['title'], fn ($v) => filled($v)));
            }
            if (array_key_exists('sort_order', $validated) && $validated['sort_order'] !== null) {
                $attachment->sort_order = $validated['sort_order'];
            }
            $attachment->save();

            foreach ($request->file('files', []) as $locale => $file) {
                $this->saveFile($attachment, $locale, $file);
            }
        });

        return $this->successResponse(new ProductAttachmentResource($attachment->fresh('files')), 'Attachment updated');
    }

    public function destroy(Product $product, ProductAttachment $attachment): JsonResponse
    {
        $this->authorize('product.update');
        $this->ensureBelongs($product, $attachment);

        // Delete file records first so physical files are removed.
        $attachment->files->each->delete();
        $attachment->delete();

        return $this->successResponse(null, 'Attachment deleted');
    }

    public function destroyFile(Product $product, ProductAttachment $attachment, string $locale): JsonResponse
    {
        $this->authorize('product.update');
        $this->ensureBelongs($product, $attachment);

        $file = $attachment->files()->where('locale', $locale)->firstOrFail();

        if ($attachment->files()->count() <= 1) {
            throw ValidationException::withMessages([
                'file' => '附件至少需要保留一個語系的檔案，如要移除請刪除整份附件',
            ]);
        }

        $file->delete();

        return $this->successResponse(new ProductAttachmentResource($attachment->fresh('files')), 'File removed');
    }

    private function rules(): array
    {
        return [
            'title.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'files.*' => [
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:'.config('uploads.product_attachment_max_kb'),
            ],
        ];
    }

    private function messages(): array
    {
        $mb = rtrim(rtrim(number_format(config('uploads.product_attachment_max_kb') / 1024, 1), '0'), '.');

        return [
            'files.*.max' => "PDF 檔案大小不能超過 {$mb} MB",
            'files.*.mimes' => '只接受 PDF 檔案',
            'files.*.mimetypes' => '只接受 PDF 檔案',
            'files.*.file' => '檔案上傳失敗，請確認檔案大小未超過限制',
            'files.*.uploaded' => "檔案上傳失敗，請確認檔案大小未超過 {$mb} MB",
            'files.min' => '至少需要上傳一個語系的 PDF',
            'files.required' => '至少需要上傳一個語系的 PDF',
        ];
    }

    private function assertKnownLocales(array $files): void
    {
        $unknown = array_diff(array_keys($files), Language::activeCodes());
        if ($unknown) {
            throw ValidationException::withMessages([
                'files' => '不支援的語系：'.implode(', ', $unknown),
            ]);
        }
    }

    private function ensureBelongs(Product $product, ProductAttachment $attachment): void
    {
        abort_unless($attachment->product_id === $product->id, 404);
    }

    /**
     * Store a PDF under a random name and create/replace the locale record.
     * The old physical file is removed only after the record points to the new one.
     */
    private function saveFile(ProductAttachment $attachment, string $locale, UploadedFile $file): void
    {
        $path = $file->store("product-attachments/{$attachment->product_id}", 'public');

        $existing = $attachment->files()->where('locale', $locale)->first();
        $oldPath = $existing?->path;

        ProductAttachmentFile::updateOrCreate(
            ['attachment_id' => $attachment->id, 'locale' => $locale],
            [
                'path' => $path,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'size' => $file->getSize(),
            ]
        );

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
