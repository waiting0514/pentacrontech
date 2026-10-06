<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $site = Project::first();

        return view('pages.about', [
            'site' => $site,
            'metaTitle' => __('frontend.about.meta_title'),
            'metaDescription' => __('frontend.about.meta_description'),
        ]);
    }
}
