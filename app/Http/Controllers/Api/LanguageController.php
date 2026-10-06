<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $languages = Language::getActive();
        return $this->successResponse($languages);
    }
}