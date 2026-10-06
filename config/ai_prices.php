<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI Pricing
    |--------------------------------------------------------------------------
    |
    | Pricing for OpenAI models (per token)
    | Source: https://openai.com/pricing
    |
    */

    'openai' => [
        'gpt-4o-mini' => [
            'input' => 0.150 / 1_000_000,   // $0.150 per 1M tokens
            'output' => 0.600 / 1_000_000,  // $0.600 per 1M tokens
        ],
        'gpt-4o' => [
            'input' => 2.50 / 1_000_000,    // $2.50 per 1M tokens
            'output' => 10.00 / 1_000_000,  // $10.00 per 1M tokens
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Photoroom Pricing
    |--------------------------------------------------------------------------
    |
    | Pricing for Photoroom API services (per request)
    |
    */

    'photoroom' => [
        'scene_generation' => 0.05,  // $0.05 per request
    ],

    /*
    |--------------------------------------------------------------------------
    | Clipdrop Pricing
    |--------------------------------------------------------------------------
    |
    | Pricing for Clipdrop API services (per request)
    |
    */

    'clipdrop' => [
        'remove_background' => 0.03,  // $0.03 per request
    ],

];
