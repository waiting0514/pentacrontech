<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    private const SIZES = [
        'main' => 1000,
        'thumb' => 500,
        'tiny' => 150,
    ];

    private const QUALITY = 80;

    /**
     * Process an uploaded image into three 1:1 JPG sizes.
     *
     * @return array{main: string, thumb: string, tiny: string}
     */
    public function processProductImage(UploadedFile $image, string $folderName = 'products'): array
    {
        $manager = new ImageManager(new Driver());
        $filename = date('Ymd') . '_' . Str::uuid() . '.jpg';
        $paths = [];

        foreach (self::SIZES as $size => $dimension) {
            $processed = $manager->read($image->getPathname())
                ->contain($dimension, $dimension)
                ->pad($dimension, $dimension, '#ffffff')
                ->toJpeg(quality: self::QUALITY);

            $relativePath = "uploads/{$folderName}/{$size}/{$filename}";
            Storage::disk('public')->put($relativePath, (string) $processed);
            $paths[$size] = $relativePath;
        }

        return $paths;
    }

    private const BANNER_WIDTHS = [
        'pc' => 1920,
        'mobile' => 800,
    ];

    private const BANNER_QUALITY = 85;

    /**
     * Process a banner image (non-square, maintain aspect ratio).
     */
    public function processBannerImage(UploadedFile $image, string $type): string
    {
        $manager = new ImageManager(new Driver());
        $width = self::BANNER_WIDTHS[$type];
        $filename = date('Ymd') . '_' . Str::uuid() . '.jpg';

        $processed = $manager->read($image->getPathname())
            ->scaleDown(width: $width)
            ->toJpeg(quality: self::BANNER_QUALITY);

        $relativePath = "uploads/banners/{$type}/{$filename}";
        Storage::disk('public')->put($relativePath, (string) $processed);

        return $relativePath;
    }

    /**
     * Delete banner images (PC + Mobile) from storage.
     */
    public function deleteBannerImages(?string $pcPath, ?string $mobilePath): void
    {
        foreach ([$pcPath, $mobilePath] as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Delete all three sizes of a product image from storage.
     */
    public function deleteProductImage(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Process a category image (resized to max 600x600, maintaining aspect ratio).
     */
    public function processCategoryImage(UploadedFile $image): string
    {
        $manager = new ImageManager(new Driver());
        $filename = date('Ymd') . '_' . Str::uuid() . '.jpg';

        $processed = $manager->read($image->getPathname())
            ->scaleDown(width: 600, height: 600)
            ->toJpeg(quality: self::QUALITY);

        $relativePath = "uploads/categories/{$filename}";
        Storage::disk('public')->put($relativePath, (string) $processed);

        return $relativePath;
    }

    /**
     * Delete a category image from storage.
     */
    public function deleteCategoryImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
