## 上下文 (Context)

目前的 `banners` 架構僅支援單一字串欄位，無法滿足不同語言顯示不同圖片或連結的需求。本設計將 `Banner` 模型重構為完全支援多語系的架構，整合 `spatie/laravel-translatable` 擴充包。

## 目標 / 非目標 (Goals / Non-Goals)

**目標:**
- 將 `banners` 表中的文字與圖片路徑欄位轉為 JSON 格式。
- 實作智慧圖片回退邏輯，優化跨市場管理。
- 提供支援多語系分頁的後台管理介面。

**非目標:**
- 本次變更不涉及輪播圖的前端動畫效果修改。
- 不改變現有的全域排序與狀態邏輯。

## 技術決策 (Decisions)

### 1. 資料模型重構
- **決策**: 將 `title`, `link_url`, `image_pc`, `image_mobile` 欄位改為 `JSON` 型態。
- **原因**: 符合專案已採用的 `Spatie\Translatable` 模式，能有效儲存各語系資料且保持資料表簡潔。

### 2. 智慧圖片回退 (Smart Image Fallback)
- **決策**: 在 `Banner` 模型實作 `image_pc_url` 與 `image_mobile_url` 屬性。
- **邏輯**: 優先讀取當前語系路徑，若為空則讀取 `config('app.fallback_locale')` 之路徑。
- **原因**: 減輕管理員工作負擔，若不需在地化圖片時只需上傳一次即可。

### 3. 後台介面更新
- **決策**: 輪播圖編輯表單將使用 `TranslatableInput` 處理標題與連結，並為圖片上傳組件新增 `v-for` 或分頁切換支援。
- **原因**: 保持與產品、新聞管理介面的一致性。

## 風險與權衡 (Risks / Trade-offs)

- **[風險] 既有資料遺失** → **[對策]** 撰寫資料遷移 (Migration) 指令，將現有的字串資料自動包裝成 JSON 格式（例如：`"old_val"` -> `{"zh-TW": "old_val"}`）。
- **[風險] API 回傳格式變更** → **[對策]** 確保 API Resource 使用 `getTranslations()` 或 `getTranslation()` 明確解析，避免前端解析 JSON 失敗。

## 遷移計畫 (Migration Plan)

1. 新增資料庫遷移：使用 `ALTER TABLE` 修改欄位型態並轉換現有資料。
2. 更新 `Banner` 模型：加入 `HasTranslations` Trait 並定義 `$translatable`。
3. 更新 `BannerController`：調整驗證邏輯與資料儲存格式。
4. 更新 Vue 組件：調整 `Banner` 管理表單。
