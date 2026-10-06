<?php

namespace App\Services;

use App\Models\User;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Collection;

class AiTextService
{
    public function __construct(
        protected AiCostControlService $costControl,
        protected RagService $ragService
    ) {}

    /**
     * Context-aware chat with AI.
     *
     * @param User $user
     * @param string $context Context type: 'product', 'news', etc.
     * @param string $userMessage User's message
     * @param string|null $ragQuery Optional query for RAG search
     * @return string AI response
     * @throws \Exception
     */
    public function chat(User $user, string $context, string $userMessage, ?string $ragQuery = null): string
    {
        // Get current project
        $project = $this->costControl->getCurrentProject();

        $featurePrice = $this->costControl->getFeaturePrice('chat');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        // Get system prompt based on context
        $systemPrompt = $this->getSystemPrompt($context);

        // Add RAG data if query provided
        if ($ragQuery) {
            $products = $this->ragService->searchProducts($ragQuery);
            $systemPrompt .= $this->buildRagContext($products);
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

            $content = $response->choices[0]->message->content;
            $inputTokens = $response->usage->promptTokens;
            $outputTokens = $response->usage->completionTokens;

            // Calculate and record cost
            $cost = $this->costControl->calculateOpenAICost('gpt-4o-mini', $inputTokens, $outputTokens);
            
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'chat',
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => $cost,
                'display_cost_twd' => $featurePrice,
                'status' => 'success',
            ]);

            $this->costControl->incrementUsage($project, $featurePrice);

            return $content;

        } catch (\Exception $e) {
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'chat',
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
     * Translate multiple fields to multiple languages in a single request.
     *
     * @param User $user
     * @param array $payload Map of fields and their source text: ['name' => '...', 'desc' => '...']
     * @param array $targetLangs Target language codes
     * @return array Nested translations: ['en' => ['name' => '...', 'desc' => '...'], 'ja' => [...]]
     */
    public function translateBatch(User $user, array $payload, array $targetLangs): array
    {
        $project = $this->costControl->getCurrentProject();
        $featurePrice = $this->costControl->getFeaturePrice('translation');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        $langNames = [
            'zh-TW' => 'Traditional Chinese (Taiwan)',
            'zh' => 'Traditional Chinese',
            'zh_CN' => 'Simplified Chinese',
            'en' => 'English',
            'ja' => 'Japanese',
            'ko' => 'Korean',
        ];

        $langList = implode(', ', array_map(fn($l) => $langNames[$l] ?? $l, $targetLangs));
        
        $prompt = "You are a professional translator. I will provide a JSON object where keys are field names and values are text in the source language. \n";
        $prompt .= "Please translate all values into these languages: {$langList}.\n";
        $prompt .= "Return a nested JSON object where top-level keys are language codes: " . implode(', ', $targetLangs) . ".\n";
        $prompt .= "Each language key should contain the translated fields.\n";
        $prompt .= "Maintain HTML tags if present in values. Keep the tone professional.";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => json_encode($payload, JSON_UNESCAPED_UNICODE)],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $inputTokens = $response->usage->promptTokens;
            $outputTokens = $response->usage->completionTokens;

            $cost = $this->costControl->calculateOpenAICost('gpt-4o-mini', $inputTokens, $outputTokens);
            
            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'translation',
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
                'feature' => 'translation',
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
     * Translate text to multiple languages.
     *
     * @param User $user
     * @param string $text Text to translate
     * @param array $targetLangs Target language codes (e.g., ['zh', 'en', 'ja'])
     * @return array Translations in format: ['zh' => '...', 'en' => '...']
     * @throws \Exception
     */
    public function translate(User $user, string $text, array $targetLangs): array
    {
        // Get current project
        $project = $this->costControl->getCurrentProject();

        $featurePrice = $this->costControl->getFeaturePrice('translation');

        if (!$this->costControl->checkBudget($project, $featurePrice)) {
            throw new \Exception('Project budget exceeded');
        }

        $langNames = [
            'zh-TW' => 'Traditional Chinese (Taiwan)',
            'zh' => 'Traditional Chinese',
            'zh_CN' => 'Simplified Chinese',
            'en' => 'English',
            'ja' => 'Japanese',
            'ko' => 'Korean',
        ];

        $instructions = "Translate the following text to these languages:\n";
        foreach ($targetLangs as $lang) {
            $langName = $langNames[$lang] ?? $lang;
            $instructions .= "- {$lang}: {$langName}\n";
        }
        $instructions .= "\nReturn a JSON object with language codes as keys.";

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional translator. Always respond with valid JSON only.'],
                    ['role' => 'user', 'content' => $instructions . "\n\nText: " . $text],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $inputTokens = $response->usage->promptTokens;
            $outputTokens = $response->usage->completionTokens;

            $cost = $this->costControl->calculateOpenAICost('gpt-4o-mini', $inputTokens, $outputTokens);

            $this->costControl->recordUsage($user, $project, [
                'provider' => 'openai',
                'feature' => 'translation',
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
                'feature' => 'translation',
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
     * Get system prompt based on context.
     *
     * @param string $context
     * @return string
     */
    protected function getSystemPrompt(string $context): string
    {
        return match($context) {
            'product' => "You are a professional Product Manager. Help users with product-related questions, provide recommendations, and explain product features clearly.",
            'news' => "You are a Social Media Editor. Create engaging content, write compelling headlines, and adapt tone for different platforms.",
            default => "You are a helpful AI assistant.",
        };
    }

    /**
     * Build RAG context from product search results.
     *
     * @param Collection $products
     * @return string
     */
    protected function buildRagContext(Collection $products): string
    {
        if ($products->isEmpty()) {
            return "";
        }

        $context = "\n\nRelevant products from our database:\n";
        foreach ($products as $product) {
            $name = is_array($product->name) ? ($product->name['zh'] ?? $product->name['en'] ?? '') : $product->name;
            $desc = is_array($product->description) ? ($product->description['zh'] ?? $product->description['en'] ?? '') : $product->description;
            
            $context .= "- {$name}: {$desc} (Price: \${$product->price})\n";
        }

        return $context;
    }
}
