<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Overtrue\Pinyin\Pinyin;

class SlugService
{
    public function generateSlug(string $name, string $table, ?int $projectId = null, ?int $excludeId = null): string
    {
        $baseSlug = $this->buildBaseSlug($name);
        return $this->ensureUnique($baseSlug, $table, $projectId, $excludeId);
    }

    protected function buildBaseSlug(string $name): string
    {
        $slug = Str::slug($name);
        if (!empty($slug)) {
            return $slug;
        }

        $pinyinSlug = Str::slug((new Pinyin())->permalink($name));
        if (!empty($pinyinSlug)) {
            return $pinyinSlug;
        }

        return 'item-' . substr(md5($name), 0, 8);
    }

    protected function ensureUnique(string $slug, string $table, ?int $projectId = null, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $table, $projectId, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, string $table, ?int $projectId = null, ?int $excludeId = null): bool
    {
        $query = DB::table($table)->where(function($q) use ($slug) {
            $q->where('slug', $slug)
              ->orWhere('slug->zh-TW', $slug)
              ->orWhere('slug->en', $slug)
              ->orWhere('slug->zh', $slug);
        });

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
