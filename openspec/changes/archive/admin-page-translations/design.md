# Admin Page Translations - Design

## Translation Key Structure

所有後台翻譯 key 使用 `admin.` 前綴，並按模組組織：

```
admin.
├── nav.*              # 側邊欄導航
├── dashboard.*        # 儀表板頁面
├── products.*         # 產品管理
├── news.*             # 最新消息
├── banners.*          # 輪播圖管理
├── users.*            # 用戶管理
├── settings.*         # 系統設定
├── usage.*            # 用量統計
├── ai.*               # AI 工具
├── auth.*             # 登入/登出
├── actions.*          # 通用操作按鈕
├── table.*            # 表格通用
├── form.*             # 表單通用
└── messages.*         # 提示訊息
```

## Implementation Pattern

### Vue Template 中使用

```vue
<!-- Before -->
<el-button>新增</el-button>
<span>產品管理</span>

<!-- After -->
<el-button>{{ $t('admin.actions.create') }}</el-button>
<span>{{ $t('admin.products.title') }}</span>
```

### JavaScript 中使用

```javascript
// 透過 getCurrentInstance 取得 $t
import { getCurrentInstance } from 'vue'

const { proxy } = getCurrentInstance()
proxy.$t('admin.messages.save_success')
```

### 動態參數

```javascript
// 翻譯檔案
'confirm_delete' => '確定要刪除「:name」嗎？'

// 使用
$t('admin.messages.confirm_delete', { name: item.name })
```

## Translation Files Update

### lang/zh-TW/admin.php 結構

```php
return [
    'nav' => [
        'dashboard' => '儀表板',
        'products' => '產品管理',
        'product_categories' => '產品分類',
        'news' => '最新消息',
        'news_categories' => '消息分類',
        'banners' => '輪播圖管理',
        'users' => '用戶管理',
        'settings' => '系統設定',
        'usage' => '用量統計',
        'ai_tools' => 'AI 工具',
    ],
    'actions' => [
        'create' => '新增',
        'edit' => '編輯',
        'delete' => '刪除',
        'save' => '儲存',
        'cancel' => '取消',
        'search' => '搜尋',
        'reset' => '重設',
        'confirm' => '確認',
        'back' => '返回',
        'upload' => '上傳',
        'preview' => '預覽',
    ],
    'table' => [
        'id' => 'ID',
        'name' => '名稱',
        'status' => '狀態',
        'sort' => '排序',
        'created_at' => '建立時間',
        'updated_at' => '更新時間',
        'actions' => '操作',
        'no_data' => '暫無資料',
    ],
    'form' => [
        'required' => '此欄位為必填',
        'select_placeholder' => '請選擇',
        'input_placeholder' => '請輸入',
    ],
    'messages' => [
        'save_success' => '儲存成功',
        'save_error' => '儲存失敗',
        'delete_success' => '刪除成功',
        'delete_error' => '刪除失敗',
        'confirm_delete' => '確定要刪除「:name」嗎？',
        'loading' => '載入中...',
    ],
    // ... 各模組專用翻譯
];
```

## No API Changes

此變更純粹為前端字串替換，不涉及任何後端 API 修改。

## Testing

- 切換語系後，所有後台頁面文字應正確顯示
- 動態參數替換應正常運作
- 無 JavaScript console 錯誤
