<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $site = SiteSetting::current();

        return view('pages.about', [
            'site' => $site,
            'metaTitle' => __('frontend.about.meta_title'),
            'metaDescription' => __('frontend.about.meta_description'),
        ]);
    }
}
