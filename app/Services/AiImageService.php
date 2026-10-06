<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

class AiImageService
{
    public function __construct(
        protected AiCostControlService $costControl
    ) {}

    /**
     * Parse product information from DM/package image using GPT-4o Vision.
     *
     * @param User $user
     * @param UploadedFile $image
     * @return array Parsed product data
     * @throws \Exception
     */
    public function parseDmImage(User $user, UploadedFile $image, ?string $locale = null): array
    {
        // Get current project
        $project = $this->costControl->getCurrentProject();
        $targetLocale = $locale ?? app()->getLocale();

        $featurePrice = $this->costControl->getFeaturePrice('vision_parse');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        // Convert image to base64
        $imageData = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType();

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a product data extraction expert. Analyze product images and extract structured information. Always respond with valid JSON only. Please extract content in the following language: {$targetLocale}. If the original text is in another language, translate it to {$targetLocale}."
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Extract the following information from this product image: name, specs (specifications), description (marketing description), meta_title (SEO title, max 60 chars), meta_description (SEO description derived from the product description, max 155 chars, plain text no HTML), meta_keywords (SEO keywords). Return as JSON.'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$imageData}"
                                ]
                            ]
                        ]
                    ]
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $inputTokens = $response->usage->promptTokens;
            $outputTokens = $response->usage->completionTokens;

            // Calculate and record cost
            $cost = $this->costControl->calculateOpenAICost('gpt-4o', $inputTokens, $outputTokens);
            
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'vision_parse',
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => $cost,
                'display_cost_twd' => $featurePrice,
                'status' => 'success',
            ]);

            $this->costControl->incrementUsage($project, $featurePrice);

            return json_decode($content, true);

        } catch (\Exception $e) {
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'vision_parse',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost_usd' => 0,
                'display_cost_twd' => 0,
                'status' => 'failed',
            ]);

            throw $e;
        }
    }

    /**
     * Parse multiple product images as a single context using GPT-4o Vision.
     * Useful for analyzing DM + packaging, multiple angles, or product variants.
     *
     * @param User $user
     * @param array $images Array of UploadedFile
     * @param string|null $locale
     * @return array Parsed product data
     * @throws \Exception
     */
    public function parseMultipleImages(User $user, array $images, ?string $locale = null): array
    {
        $project = $this->costControl->getCurrentProject();
        $targetLocale = $locale ?? app()->getLocale();
        $imageCount = count($images);

        $featurePrice = $this->costControl->getFeaturePrice('vision_parse_multi');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        // Build image content array for GPT-4o Vision
        $imageContents = [];
        $imageContents[] = [
            'type' => 'text',
            'text' => "Analyze these {$imageCount} product images together. They show the same product from different angles, packaging, DM/flyer, or different color/size variants. Extract comprehensive product information by combining details from ALL images. Return JSON with: name, specs (detailed specifications from packaging/labels), description (marketing description from DM), meta_title (SEO title, max 60 chars), meta_description (SEO description derived from the product description, max 155 chars, plain text no HTML), meta_keywords (SEO keywords). If you detect multiple variants (colors, sizes), include a 'variants' array.",
        ];

        foreach ($images as $image) {
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();
            $imageContents[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$imageData}",
                ],
            ];
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a product data extraction expert. Analyze multiple product images and extract comprehensive structured information by combining details from all images. Always respond with valid JSON only. Please extract content in the following language: {$targetLocale}. If the original text is in another language, translate it to {$targetLocale}.",
                    ],
                    [
                        'role' => 'user',
                        'content' => $imageContents,
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $inputTokens = $response->usage->promptTokens;
            $outputTokens = $response->usage->completionTokens;

            $cost = $this->costControl->calculateOpenAICost('gpt-4o', $inputTokens, $outputTokens);

            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'vision_parse_multi',
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => $cost,
                'display_cost_twd' => $featurePrice,
                'status' => 'success',
            ]);

            $this->costControl->incrementUsage($project, $featurePrice);

            $parsedData = json_decode($content, true);

            return [
                'data' => $parsedData,
                'usage' => [
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                    'cost_usd' => $cost,
                    'image_count' => $imageCount,
                ],
            ];

        } catch (\Exception $e) {
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'vision_parse_multi',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost_usd' => 0,
                'display_cost_twd' => 0,
                'status' => 'failed',
            ]);

            throw $e;
        }
    }

    /**
     * Remove background from image using Clipdrop API.
     *
     * @param User $user
     * @param UploadedFile $image
     * @return string URL of processed image
     * @throws \Exception
     */
    public function removeBackground(User $user, UploadedFile $image): string
    {
        // Get current project
        $project = $this->costControl->getCurrentProject();

        $featurePrice = $this->costControl->getFeaturePrice('remove_bg');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        $apiKey = config('services.clipdrop.api_key');

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->attach(
                'image_file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )->post('https://clipdrop-api.co/remove-background/v1');

            if (!$response->successful()) {
                throw new \Exception('Clipdrop API request failed: ' . $response->body());
            }

            $filename = 'processed_' . time() . '_' . $image->getClientOriginalName();
            Storage::disk('public')->put('images/' . $filename, $response->body());

            $cost = $this->costControl->estimateImageCost('clipdrop');

            $this->costControl->recordUsage($user, $project, [
                'provider' => 'clipdrop',
                'feature' => 'remove_bg',
                'input_tokens' => null,
                'output_tokens' => null,
                'cost_usd' => $cost,
                'display_cost_twd' => $featurePrice,
                'status' => 'success',
            ]);

            $this->costControl->incrementUsage($project, $featurePrice);

            return Storage::url('images/' . $filename);

        } catch (\Exception $e) {
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'clipdrop',
                'feature' => 'remove_bg',
                'input_tokens' => null,
                'output_tokens' => null,
                'cost_usd' => 0,
                'display_cost_twd' => 0,
                'status' => 'failed',
            ]);

            throw $e;
        }
    }

    /**
     * Generate scene with product using Photoroom API.
     *
     * @param User $user
     * @param UploadedFile $image
     * @param string $prompt Scene description
     * @return string URL of generated image
     * @throws \Exception
     */
    public function generateScene(User $user, UploadedFile $image, string $prompt): string
    {
        // Get current project
        $project = $this->costControl->getCurrentProject();

        $featurePrice = $this->costControl->getFeaturePrice('scene_gen');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        $apiKey = config('services.photoroom.api_key');

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->attach(
                'image_file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )->post('https://sdk.photoroom.com/v1/segment', [
                'prompt' => $prompt,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Photoroom API request failed: ' . $response->body());
            }

            $filename = 'scene_' . time() . '_' . $image->getClientOriginalName();
            Storage::disk('public')->put('images/' . $filename, $response->body());

            $cost = $this->costControl->estimateImageCost('photoroom');

            $this->costControl->recordUsage($user, $project, [
                'provider' => 'photoroom',
                'feature' => 'scene_gen',
                'input_tokens' => null,
                'output_tokens' => null,
                'cost_usd' => $cost,
                'display_cost_twd' => $featurePrice,
                'status' => 'success',
            ]);

            $this->costControl->incrementUsage($project, $featurePrice);

            return Storage::url('images/' . $filename);

        } catch (\Exception $e) {
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'photoroom',
                'feature' => 'scene_gen',
                'input_tokens' => null,
                'output_tokens' => null,
                'cost_usd' => 0,
                'display_cost_twd' => 0,
                'status' => 'failed',
            ]);

            throw $e;
        }
    }
}
