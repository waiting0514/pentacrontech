<?php

namespace Tests\Unit;

use App\Support\YouTubeUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class YouTubeUrlTest extends TestCase
{
    public static function validUrls(): array
    {
        $id = 'dQw4w9WgXcQ';

        return [
            'watch' => ["https://www.youtube.com/watch?v={$id}"],
            'watch no www' => ["https://youtube.com/watch?v={$id}"],
            'mobile' => ["https://m.youtube.com/watch?v={$id}"],
            'music' => ["https://music.youtube.com/watch?v={$id}"],
            'short link' => ["https://youtu.be/{$id}"],
            'short link with t' => ["https://youtu.be/{$id}?t=30"],
            'shorts' => ["https://www.youtube.com/shorts/{$id}"],
            'embed' => ["https://www.youtube.com/embed/{$id}"],
            'nocookie embed' => ["https://www.youtube-nocookie.com/embed/{$id}"],
            'live' => ["https://www.youtube.com/live/{$id}"],
            'extra params' => ["https://www.youtube.com/watch?v={$id}&t=30s&list=PL123"],
            'v param not first' => ["https://www.youtube.com/watch?list=PL123&v={$id}"],
            'no scheme' => ["www.youtube.com/watch?v={$id}"],
            'surrounding space' => ["  https://youtu.be/{$id}  "],
            'http' => ["http://www.youtube.com/watch?v={$id}"],
        ];
    }

    #[DataProvider('validUrls')]
    public function test_extracts_id_from_valid_urls(string $url): void
    {
        $this->assertSame('dQw4w9WgXcQ', YouTubeUrl::extractId($url));
    }

    public static function invalidUrls(): array
    {
        return [
            'empty' => [''],
            'null-like spaces' => ['   '],
            'other host' => ['https://example.com/watch?v=dQw4w9WgXcQ'],
            'lookalike host' => ['https://youtube.com.evil.com/watch?v=dQw4w9WgXcQ'],
            'vimeo' => ['https://vimeo.com/123456789'],
            'javascript scheme' => ['javascript:alert(1)'],
            'channel page' => ['https://www.youtube.com/@somechannel'],
            'playlist only' => ['https://www.youtube.com/playlist?list=PL123'],
            'watch without v' => ['https://www.youtube.com/watch'],
            'id too short' => ['https://youtu.be/abc'],
            'id too long' => ['https://youtu.be/dQw4w9WgXcQextra'],
            'bare id' => ['dQw4w9WgXcQ'],
            'invalid chars' => ['https://youtu.be/dQw4w9Wg<cQ'],
        ];
    }

    #[DataProvider('invalidUrls')]
    public function test_rejects_invalid_urls(string $url): void
    {
        $this->assertNull(YouTubeUrl::extractId($url));
    }

    public function test_null_input_returns_null(): void
    {
        $this->assertNull(YouTubeUrl::extractId(null));
    }
}
