# Project Spec

## 1. Overview
The `Project` model represents a specific website or client instance within the system. It contains global settings, budget information, and contact details.

## 2. Schema Definition

### 2.1 Basic Information
- `name`: Translatable. The display name of the site.
- `domain`: String. Used for SEO and site identification.
- `description`: Text. Internal description of the project.

### 2.2 Budget & Usage
- `yearly_budget_twd`: Decimal (10, 2). 年度 AI 預算上限，單位 TWD，預設 1000。
- `yearly_usage_twd`: Decimal (10, 2). 當年度已使用金額（累計固定單價），單位 TWD，預設 0。
- `is_active`: Boolean. Whether the project is active.

### 2.3 Contact Information (Standardized)
The system SHALL use the `contact_` prefix for all site-wide contact details. The `company_` prefix is deprecated and SHOULD NOT be used.

- `contact_email`: String. Public contact email address.
- `contact_phone`: String. Public contact phone number.
- `contact_address`: Translatable Text. Public physical address.
- `geo_lat`: Decimal (10, 7). Latitude for map display.
- `geo_lng`: Decimal (10, 7). Longitude for map display.

### 2.4 SEO & Social
- `meta_title`: Translatable.
- `meta_description`: Translatable.
- `meta_keywords`: Translatable.
- `logo_url`: String.
- `social_links`: JSON.

### 2.5 Maintenance Mode
- `maintenance_mode`: Boolean.
- `maintenance_message`: Translatable.

### 2.6 Security
- `recaptcha_site_key`: String.
- `recaptcha_secret_key`: String.

## 3. Implementation Rules
- ALL contact information displayed on the frontend MUST pull from the `contact_` fields.
- Admin settings UI MUST bind to `contact_` fields.
- Migration `2024_01_01_000001_create_projects_table.php` is the source of truth for the schema.

## 4. Requirements

### Requirement: 功能開關
新增 AI 功能開關設定，以控制專案層級的 AI 服務存取。

#### Scenario: 設定 AI 開關
- **WHEN** 管理者更新專案設定
- **THEN** 系統 SHALL 儲存 `is_ai_enabled` (boolean) 欄位，預設為 `true`