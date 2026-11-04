<?php

namespace App\Http\Requests;

use App\Models\Video;
use App\Support\YouTubeUrl;
use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('video.manage') ?? false;
    }

    public function rules(): array
    {
        $creating = $this->isMethod('POST');
        $current = $this->route('video');
        $required = $creating ? 'required' : 'sometimes';

        return [
            'youtube_url' => [$required, 'string', 'max:500', function ($attribute, $value, $fail) use ($current) {
                $id = YouTubeUrl::extractId($value);
                if ($id === null) {
                    $fail('請輸入有效的 YouTube 影片網址。');

                    return;
                }

                $exists = Video::where('youtube_id', $id)
                    ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
                    ->exists();
                if ($exists) {
                    $fail('這支影片已經新增過了。');
                }
            }],
            'title' => [$required, 'array'],
            'title.zh-TW' => [$required, 'string', 'max:255'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'slug' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'array'],
            'meta_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'array'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'array'],
            'meta_keywords.*' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Validated attributes mapped to model columns (youtube_url -> youtube_id).
     */
    public function videoData(): array
    {
        $data = $this->validated();

        if (isset($data['youtube_url'])) {
            $data['youtube_id'] = YouTubeUrl::extractId($data['youtube_url']);
            unset($data['youtube_url']);
        }

        return $data;
    }
}
