<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    use ApiResponse;

    /**
     * Get translations for a specific locale with optional scope filtering.
     *
     * @param Request $request
     * @param string $locale
     * @return JsonResponse
     */
    public function index(Request $request, string $locale): JsonResponse
    {
        // Validate locale exists
        $langPath = lang_path();
        $jsonFile = "{$langPath}/{$locale}.json";
        $localePath = "{$langPath}/{$locale}";

        if (!File::exists($jsonFile) && !File::isDirectory($localePath)) {
            return $this->errorResponse("Locale '{$locale}' is not available", 'LOCALE_NOT_FOUND', 404);
        }

        // Parse scope parameter
        $scopes = [];
        if ($request->has('scope')) {
            $scopes = array_filter(array_map('trim', explode(',', $request->input('scope'))));
        }

        // Generate cache key
        $cacheKey = "translations.{$locale}." . md5(implode(',', $scopes));

        // Try to get from cache (1 hour)
        $translations = Cache::remember($cacheKey, 3600, function () use ($locale, $scopes, $jsonFile, $localePath) {
            return $this->loadTranslations($locale, $scopes, $jsonFile, $localePath);
        });

        // Return with cache headers
        return response()->json($translations)
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Load and merge translations from JSON and PHP files.
     *
     * @param string $locale
     * @param array $scopes
     * @param string $jsonFile
     * @param string $localePath
     * @return array
     */
    private function loadTranslations(string $locale, array $scopes, string $jsonFile, string $localePath): array
    {
        $translations = [];

        // 1. Always load shared JSON translations
        if (File::exists($jsonFile)) {
            $jsonContent = File::get($jsonFile);
            $translations = json_decode($jsonContent, true) ?? [];
        }

        // 2. Load PHP module translations based on scope
        if (!empty($scopes) && File::isDirectory($localePath)) {
            foreach ($scopes as $scope) {
                $phpFile = "{$localePath}/{$scope}.php";

                if (File::exists($phpFile)) {
                    $moduleTranslations = require $phpFile;

                    if (is_array($moduleTranslations)) {
                        // Flatten and add prefix
                        $flattened = $this->flattenWithPrefix($moduleTranslations, $scope);
                        $translations = array_merge($translations, $flattened);
                    }
                }
                // Silently ignore non-existent scope files
            }
        }

        return $translations;
    }

    /**
     * Flatten nested array and add prefix to keys.
     *
     * @param array $array
     * @param string $prefix
     * @return array
     */
    private function flattenWithPrefix(array $array, string $prefix): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = "{$prefix}.{$key}";

            if (is_array($value)) {
                // Recursively flatten nested arrays
                $nested = $this->flattenWithPrefix($value, $newKey);
                $result = array_merge($result, $nested);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Clear translation cache for a locale.
     *
     * @param string $locale
     * @return JsonResponse
     */
    public function clearCache(string $locale): JsonResponse
    {
        Cache::flush();

        return $this->successResponse(null, "Translation cache cleared for {$locale}");
    }
}
