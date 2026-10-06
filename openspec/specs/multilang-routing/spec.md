# multilang-routing

## Purpose
提供一套通用的多語系路由與切換機制，支援動態 URL 前綴、SEO 正規化以及前端語言切換。

## Requirements

### Requirement: 語言配置管理 (Language Configuration Management)
系統必須能夠透過資料庫動態管理支援的語言，區分預設語言與其他啟用語言。

#### Scenario: 讀取語言設定
- **WHEN** 系統啟動或處理請求時
- **THEN** 應從 `languages` 資料表讀取所有 `is_active=true` 的語言
- **AND** 應識別出唯一的一個 `is_default=true` 的語言

#### Scenario: 單語系專案設定
- **WHEN** 資料表中只有一筆 `is_active=true` 的記錄 (例如 zh-TW)
- **THEN** 該記錄必須同時標記為 `is_default=true`

### Requirement: 智慧路由中介層 (Intelligent Routing Middleware)
系統必須實作一個全域中介層 `SetLocale`，負責解析 URL 的第一個路徑區段 (Segment 1)，並根據語言設定決定應用程式語系 (Locale) 或執行重導向。

#### Scenario: 存取非預設語言前綴 (Case A)
- **WHEN** 專案啟用多語系 (如 zh-TW, en)
- **AND** 使用者存取帶有有效語言前綴的 URL (例如 `/en/products`)
- **AND** 該前綴 (en) **不是** 預設語言
- **THEN** `App::getLocale()` 應被設定為 `en`
- **AND** 請求應正常處理 (回傳 200 OK)

#### Scenario: 存取預設語言前綴 - SEO 正規化 (Case B)
- **WHEN** 專案啟用多語系 (如 zh-TW, en)，且 zh-TW 為預設
- **AND** 使用者存取帶有預設語言前綴的 URL (例如 `/zh-TW/products`)
- **THEN** 系統應回應 HTTP 301 Redirect
- **AND** 重導向目標應為移除前綴後的 URL (例如 `/products`)，並保留原有的 Query String

#### Scenario: 存取無前綴 URL (Case C)
- **WHEN** 使用者存取沒有語言前綴的 URL (例如 `/products`)
- **THEN** `App::getLocale()` 應被設定為資料庫中定義的預設語言 (例如 zh-TW)
- **AND** 系統應設定全域 URL 預設值，確保生成的連結 (透過 `route()`) 對於預設語言不會包含語言參數

### Requirement: 路由群組與 URL 生成 (Routing Group & URL Generation)
前台路由必須定義在動態群組內，以便自動處理語言參數，並確保生成的連結符合 SEO 規範。

#### Scenario: 生成預設語言連結
- **WHEN** 當前語系為預設語言
- **AND** 開發者在 Blade 中呼叫 `route('products.show', ['id'=>1])`
- **THEN** 生成的 URL 應不包含語言前綴 (例如 `/products/1`)

#### Scenario: 生成非預設語言連結
- **WHEN** 當前語系為非預設語言 (例如 en)
- **AND** 開發者在 Blade 中呼叫 `route('products.show', ['id'=>1])`
- **THEN** 生成的 URL 應包含語言前綴 (例如 `/en/products/1`)

### Requirement: 前端語言切換元件 (Language Switcher Component)
提供一個 Blade 元件 `<x-lang-switcher />`，根據當前啟用的語言數量自動決定顯示方式。

#### Scenario: 單語系隱藏
- **WHEN** 啟用的語言數量 <= 1
- **THEN** 該元件不應渲染任何 HTML 內容

#### Scenario: 多語系切換連結
- **WHEN** 啟用的語言數量 > 1
- **THEN** 應列出所有啟用語言的切換連結
- **AND** 每個連結應指向當前路由的對應語言版本 (例如在 `/en/products`點擊 "中文"，應連至 `/products`)
- **AND** 預設語言的連結 URL 不應包含前綴
