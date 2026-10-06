# Frontend Page Translations - Design

## Translation Key Structure

所有前台翻譯 key 使用 `frontend.` 前綴，並按模組組織：

```
frontend.
├── nav.*              # 導航列
├── footer.*           # 頁尾
├── home.*             # 首頁
├── products.*         # 產品頁
├── news.*             # 最新消息頁
├── contact.*          # 聯絡我們
├── common.*           # 通用文字
└── buttons.*          # 通用按鈕
```

## Implementation Pattern

### Blade Template 中使用

```blade
{{-- Before --}}
<a href="/">首頁</a>
<span>最新消息</span>

{{-- After --}}
<a href="/">{{ __('frontend.nav.home') }}</a>
<span>{{ __('frontend.nav.news') }}</span>
```

### 動態參數

```php
// 翻譯檔案
'showing_results' => '顯示 :count 筆結果'

// 使用
{{ __('frontend.products.showing_results', ['count' => $products->count()]) }}
```

### 複數形式

```php
// 翻譯檔案
'items' => '{0} 沒有項目|{1} 1 個項目|[2,*] :count 個項目'

// 使用
{{ trans_choice('frontend.common.items', $count, ['count' => $count]) }}
```

## Translation Files Update

### lang/zh-TW/frontend.php 結構

```php
return [
    'nav' => [
        'home' => '首頁',
        'products' => '產品',
        'news' => '最新消息',
        'contact' => '聯絡我們',
        'language' => '語言',
    ],
    'footer' => [
        'copyright' => '© :year :company 版權所有',
        'address' => '地址',
        'phone' => '電話',
        'email' => '電子郵件',
        'follow_us' => '關注我們',
    ],
    'home' => [
        'hero_title' => '歡迎',
        'hero_subtitle' => '探索我們的產品',
        'featured_products' => '精選產品',
        'latest_news' => '最新消息',
        'view_all' => '查看全部',
    ],
    'products' => [
        'title' => '產品',
        'categories' => '產品分類',
        'all_products' => '所有產品',
        'details' => '產品詳情',
        'inquiry' => '詢價',
        'add_to_cart' => '加入購物車',
        'no_products' => '目前沒有產品',
    ],
    'news' => [
        'title' => '最新消息',
        'categories' => '消息分類',
        'all_news' => '所有消息',
        'read_more' => '閱讀更多',
        'published_at' => '發布於 :date',
        'no_news' => '目前沒有消息',
    ],
    'contact' => [
        'title' => '聯絡我們',
        'name' => '姓名',
        'email' => '電子郵件',
        'phone' => '電話',
        'message' => '訊息',
        'submit' => '送出',
        'success' => '訊息已送出，我們會盡快回覆您',
    ],
    'common' => [
        'back' => '返回',
        'loading' => '載入中...',
        'error' => '發生錯誤',
        'no_results' => '沒有結果',
    ],
    'buttons' => [
        'submit' => '送出',
        'cancel' => '取消',
        'view' => '查看',
        'more' => '更多',
    ],
];
```

## Multi-language Routing Integration

前台頁面透過 URL prefix 切換語系：

```
/zh-TW/products     → 中文產品頁
/en/products        → 英文產品頁
```

`SetLocale` middleware 會自動設定 `App::setLocale()`，Blade 的 `__()` 會自動使用正確語系。

## No API Changes

此變更純粹為前端字串替換，不涉及任何後端 API 修改。

## Testing

- 訪問 `/zh-TW/*` 路徑，所有文字應顯示中文
- 訪問 `/en/*` 路徑，所有文字應顯示英文
- 語系切換器應正常運作
- 無 PHP 錯誤
