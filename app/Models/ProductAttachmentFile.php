<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductAttachmentFile extends Model
{
    protected $fillable = [
        'attachment_id',
        'locale',
        'path',
        'original_name',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function booted(): void
    {
        // Remove the physical file whenever a file record goes away.
        static::deleted(function (ProductAttachmentFile $file) {
            try {
                Storage::disk('public')->delete($file->path);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete attachment file', ['path' => $file->path, 'error' => $e->getMessage()]);
            }
        });
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(ProductAttachment::class, 'attachment_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        $number = $i === 0 ? (string) (int) $size : rtrim(rtrim(number_format($size, 1), '0'), '.');

        return $number.' '.$units[$i];
    }
}
