<?php

namespace App\Http\Controllers\Api;

use App\Services\AiTextService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class AiTextController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AiTextService $aiTextService
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $this->authorize('ai.use');
        $validated = $request->validate([
            'context' => 'required|string|in:product,news',
            'message' => 'required|string',
            'rag_query' => 'nullable|string',
        ]);

        $response = $this->aiTextService->chat(
            $request->user(),
            $validated['context'],
            $validated['message'],
            $validated['rag_query'] ?? null
        );

        return $this->successResponse(['response' => $response]);
    }

    public function translate(Request $request): JsonResponse
    {
        $this->authorize('ai.use');
        $validated = $request->validate([
            'text' => 'sometimes|string',
            'batch' => 'sometimes|array',
            'target_langs' => 'required|array',
            'target_langs.*' => 'string',
        ]);

        if ($request->has('batch')) {
            $translations = $this->aiTextService->translateBatch(
                $request->user(),
                $validated['batch'],
                $validated['target_langs']
            );
        } else {
            $translations = $this->aiTextService->translate(
                $request->user(),
                $validated['text'],
                $validated['target_langs']
            );
        }

        return $this->successResponse(['translations' => $translations]);
    }
}
