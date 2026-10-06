<?php

namespace App\Http\Controllers\Api;

use App\Services\AiImageService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AiImageController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AiImageService $aiImageService
    ) {}

    public function parseImage(Request $request): JsonResponse
    {
        $this->authorize('ai.use');
        $request->validate([
            'image' => 'required|image|max:10240',
            'locale' => 'nullable|string|max:10',
        ]);

        $parsedData = $this->aiImageService->parseDmImage(
            $request->user(),
            $request->file('image'),
            $request->locale
        );

        return $this->successResponse(['data' => $parsedData]);
    }

    /**
     * Parse multiple images as a single context for comprehensive product analysis.
     */
    public function parseImages(Request $request): JsonResponse
    {
        $this->authorize('ai.use');
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|max:10240',
            'locale' => 'nullable|string|max:10',
        ]);

        $result = $this->aiImageService->parseMultipleImages(
            $request->user(),
            $request->file('images'),
            $request->locale
        );

        return $this->successResponse($result);
    }

    public function removeBackground(Request $request): JsonResponse
    {
        $this->authorize('ai.use');
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $imageUrl = $this->aiImageService->removeBackground(
            $request->user(),
            $request->file('image')
        );

        return $this->successResponse(['image_url' => $imageUrl]);
    }

    public function generateScene(Request $request): JsonResponse
    {
        $this->authorize('ai.use');
        $request->validate([
            'image' => 'required|image|max:10240',
            'prompt' => 'required|string|max:500',
        ]);

        $imageUrl = $this->aiImageService->generateScene(
            $request->user(),
            $request->file('image'),
            $request->prompt
        );

        return $this->successResponse(['image_url' => $imageUrl]);
    }
}
