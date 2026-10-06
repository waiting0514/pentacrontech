<?php

/**
 * AI 功能固定單價定價表 (TWD)
 *
 * 每個 AI 功能的固定計費金額，用於扣減專案年度預算。
 * 調價方式：修改此檔案後部署即生效。
 */
return [
    'vision_parse'       => 10, // AI 辨識（單圖）
    'vision_parse_multi' => 15, // AI 辨識（多圖）
    'remove_bg'          => 5,  // 去背
    'scene_gen'          => 8,  // 情境圖生成
    'translation'        => 3,  // 翻譯
    'chat'               => 3,  // 文字生成

    // 未定義功能的預設價格
    'default'            => 10,
];
