<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VideoController extends Controller
{
    /**
     * Display video listing.
     */
    public function index(): View
    {
        $site = SiteSetting::current();

        $videos = Video::active()
            ->ordered()
            ->paginate(12);

        return view('videos.index', [
            'site' => $site,
            'videos' => $videos,
            'metaTitle' => __('frontend.video.title'),
            'metaDescription' => __('frontend.video.subtitle'),
        ]);
    }

    /**
     * Display video detail.
     */
    public function show(int $id, string $slug): View|RedirectResponse
    {
        $site = SiteSetting::current();

        $video = Video::active()->findOrFail($id);

        // Redirect to canonical URL when slug is outdated
        if ($slug !== ($video->slug ?: 'video')) {
            return redirect()->to($video->url, 301);
        }

        $relatedVideos = Video::active()
            ->ordered()
            ->where('id', '!=', $video->id)
            ->take(3)
            ->get();

        $description = (string) $video->description;

        return view('videos.show', [
            'site' => $site,
            'video' => $video,
            'relatedVideos' => $relatedVideos,
            'metaTitle' => $video->meta_title ?: $video->title,
            'metaDescription' => $video->meta_description ?: Str::limit(strip_tags($description), 160),
            'metaKeywords' => $video->meta_keywords ?: '',
            'ogImage' => $video->thumbnail_url,
            'ogType' => 'video.other',
        ]);
    }

    /**
     * Redirect to SEO-friendly URL.
     */
    public function redirectToSlug(int $id): RedirectResponse
    {
        $video = Video::active()->findOrFail($id);

        return redirect()->to($video->url, 301);
    }
}
