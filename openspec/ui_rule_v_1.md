## OpenSpec: Existing UI Rules (v1.1)

> Source of Truth: `layouts/app.blade.php`, `products/show.blade.php`
> This spec is **extracted**, not invented. Any class listed below exists in code.

---

### 1. Colors

#### Primary Brand Color
- **Hex:** `#f5a623`
- **Usage:**
  - Price highlight
  - Active / hover links
  - Primary CTA button accent
- **Classes / Patterns:**
  - `text-[#f5a623]`
  - `bg-[#f5a623]`
  - `hover:text-[#f5a623]`
  - `border-[#f5a623]`

#### Background Colors
- **Page Background:**
  - `bg-white`
- **Section / Soft Background:**
  - `bg-gray-50`
  - `bg-gray-100`
- **Card / Image Container:**
  - `bg-gray-100`

#### Text Colors
- **Primary Heading Text:**
  - `text-[#1a1a1a]`
- **Body Text:**
  - `text-gray-800`
  - `text-gray-700`
- **Muted / Secondary:**
  - `text-gray-500`
  - `text-gray-300`

---

### 2. Typography

#### Global Font
- `font-sans`
- Google Fonts:
  - `Inter` (400–900)
  - `Noto Sans TC` (400–900)

#### Headings
- **H1 (Product Title):**
  - `text-3xl md:text-4xl`
  - `font-black`
  - `text-[#1a1a1a]`
  - `tracking-tight`
  - `mb-4`

- **H2 (Section Title):**
  - `text-2xl`
  - `font-black`
  - `text-[#1a1a1a]`
  - `mb-8`

- **H3 (Card Title):**
  - `font-bold`
  - `text-[#1a1a1a]`

#### Body Text
- Default:
  - `text-base`
  - `text-gray-800`
- Rich content:
  - `prose prose-lg max-w-none`

---

### 3. Components

#### Buttons

**Primary Action Button**
- Classes:
  - `ind-btn`
  - `inline-flex items-center`
  - `px-8 py-4`
  - `rounded`
  - `text-lg`
- Color behavior defined in `.ind-btn` (project-level utility)

**Image Selector Button**
- `rounded-lg`
- `overflow-hidden`
- `border-2`
- States:
  - Active: `border-[#f5a623]`
  - Hover: `hover:border-[#f5a623]`

---

#### Forms (Observed Patterns)
> No direct form inputs in this page. Reserved for v1.2

---

#### Cards

**Product / Related Card (`ind-card`)**
- Container:
  - `rounded-lg`
  - `overflow-hidden`
  - `group`
- Image:
  - `w-full h-40 object-cover`
  - `group-hover:scale-105`
  - `transition duration-300`
- Content Padding:
  - `p-4`

---

### 4. Spacing & Layout

#### Standard Container
- `max-w-7xl mx-auto`
- Horizontal Padding:
  - `px-4 sm:px-6 lg:px-8`

#### Vertical Rhythm
- Page Section:
  - `py-12 md:py-16`
- Section Separation:
  - `mt-16 pt-16 border-t border-gray-200`

#### Grid System
- Main Layout:
  - `grid grid-cols-1 lg:grid-cols-2 gap-12`
- Related Products:
  - `grid grid-cols-1 md:grid-cols-3 gap-6`

---

### 5. UI Motifs & Reusable Patterns

- Accent Divider:
  - `w-12 h-1 bg-[#f5a623] mb-4`
- Borders:
  - `border border-gray-200`
  - `border-t border-gray-200`
- Rounded:
  - `rounded-lg`

---

### OpenSpec Rules

1. **All new CTAs must use `ind-btn`**
2. **All highlight values (price, active) use `#f5a623`**
3. **Cards use `rounded-lg + overflow-hidden`**
4. **Max width is always `max-w-7xl`**
5. **No arbitrary colors unless added to this spec**

---

> Next upgrade suggestions:
> - Extract `.ind-btn` / `.ind-card` CSS source
> - Add Forms spec from contact page
> - Convert to OpenSpec JSON / AI-readable schema



---
## Forms & Inputs Specification (v1.2)

### Form Container
- 背景：`bg-white`
- 邊框：`border border-gray-200`
- 頂部品牌線：`border-t: 3px solid #f5a623`
- 圓角：`rounded-lg`
- Padding：`p-8`

### Labels
- 字級：`text-sm`
- 字重：`font-bold`
- 顏色：`#1a1a1a`
- 樣式：`uppercase tracking-wide`
- 與欄位間距：`mb-2`

### Inputs / Textarea (`.ind-input`)
- 圓角：`rounded-lg`
- Padding：`px-4 py-3`
- 寬度：`w-full`
- 預設邊框：中性灰（由 ind-input 控制）
- Focus 狀態：品牌色強調（#f5a623）

### Validation States
- Error box：
  - 背景：`bg-red-50`
  - 文字：`text-red-700`
  - 邊框：`border border-red-200`
- Success box：
  - 背景：`bg-green-50`
  - 文字：`text-green-700`
  - 邊框：`border border-green-200`

### Primary Submit Button (`.ind-btn`)
- 寬度：`w-full`
- Padding：`px-6 py-4`
- 字級：`text-lg`
- Disabled：`opacity-50`
- Loading state：文字切換（送出中…）

### Interaction Patterns
- Alpine.js 用於：
  - Submit loading state
  - Disable 重複提交
- reCAPTCHA：
  - 僅在 site key 存在時顯示

### Adjacent Info Card (Dark Variant)
- 背景：`#1a1a1a`
- 文字：白色為主，輔助文字 `text-gray-400`
- Icon 色：品牌色 `#f5a623`

### Map Embed
- 容器：`rounded-lg overflow-hidden`
- 邊框：`border border-gray-200`
- 高度：300px

---
**This section finalizes the core form system.**
