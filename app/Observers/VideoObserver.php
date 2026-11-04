<?php

namespace App\Observers;

use App\Models\Video;
use Illuminate\Support\Str;
use Overtrue\Pinyin\Pinyin;

class VideoObserver
{
    public function creating(Video $video): void
    {
        $this->resolveSlug($video);
    }

    public function updating(Video $video): void
    {
        if ($video->isDirty('slug') || empty($video->slug)) {
            $this->resolveSlug($video);
        }
    }

    protected function resolveSlug(Video $video): void
    {
        $base = $video->slug ? Str::slug($video->slug) : $this->slugFromTitle($video);

        if ($base === '') {
            $base = $this->slugFromTitle($video);
        }

        $video->slug = $this->ensureUnique($base, $video);
    }

    protected function slugFromTitle(Video $video): string
    {
        $titles = $video->getTranslations('title');
        $title = $titles['zh-TW'] ?? $titles['en'] ?? (reset($titles) ?: '');

        $slug = Str::slug($title);
        if ($slug === '') {
            $slug = Str::slug((new Pinyin)->permalink($title));
        }

        return $slug !== '' ? $slug : 'video-'.substr(md5($title.$video->youtube_id), 0, 8);
    }

    protected function ensureUnique(string $slug, Video $video): string
    {
        $candidate = $slug;
        $counter = 1;

        while (Video::where('slug', $candidate)
            ->when($video->exists, fn ($q) => $q->where('id', '!=', $video->id))
            ->exists()) {
            $candidate = $slug.'-'.$counter++;
        }

        return $candidate;
    }
}
