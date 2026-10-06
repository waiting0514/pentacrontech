# Laravel Sanctum 認證設定指南

## 問題說明

API 路由使用 `auth:sanctum` middleware 保護,需要有效的 Bearer Token 才能訪問。

---

## 快速解決方案

### 選項 1: 建立測試使用者並取得 Token

#### 1. 建立測試使用者

```bash
./vendor/bin/sail artisan tinker
```

在 Tinker 中執行:

```php
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'monthly_budget_usd' => 50.00
]);

// 生成 Token
$token = $user->createToken('test-token')->plainTextToken;
echo $token;
```

複製輸出的 Token。

#### 2. 在前端設定 Token

開啟瀏覽器 Console (F12),執行:

```javascript
localStorage.setItem('api_token', 'YOUR_TOKEN_HERE');
```

然後重新整理頁面。

---

### 選項 2: 使用登入 API

#### 1. 建立測試使用者 (同上)

#### 2. 呼叫登入 API

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

回應會包含 Token:

```json
{
  "token": "1|xxxxxxxxxxxxx",
  "user": {...}
}
```

#### 3. 在前端儲存 Token

```javascript
localStorage.setItem('api_token', 'YOUR_TOKEN_HERE');
```

---

### 選項 3: 暫時移除認證 (僅開發環境)

如果只是想快速測試前端,可以暫時註解掉 `auth:sanctum` middleware:

編輯 `routes/api.php`:

```php
// 註解掉這行
// Route::middleware('auth:sanctum')->group(function () {

// 改為
Route::group(function () {
```

**⚠️ 警告**: 這會讓所有 API 公開,僅供開發測試使用!

---

## 完整的認證流程 (生產環境)

### 1. 建立登入頁面

建立 `resources/js/pages/Login.vue`:

```vue
<script setup>
import { ref } from 'vue';
import axios from 'axios';

const email = ref('test@example.com');
const password = ref('password');
const error = ref(null);

const login = async () => {
  try {
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value
    });
    
    localStorage.setItem('api_token', response.data.token);
    window.location.href = '/dashboard';
  } catch (err) {
    error.value = '登入失敗,請檢查帳號密碼';
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
      <h2 class="text-2xl font-bold mb-6">登入</h2>
      
      <form @submit.prevent="login">
        <input
          v-model="email"
          type="email"
          placeholder="Email"
          class="w-full px-4 py-2 mb-4 border rounded"
        />
        <input
          v-model="password"
          type="password"
          placeholder="Password"
          class="w-full px-4 py-2 mb-4 border rounded"
        />
        <button
          type="submit"
          class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600"
        >
          登入
        </button>
      </form>
      
      <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>
    </div>
  </div>
</template>
```

### 2. 更新路由

在 `routes/web.php` 加入:

```php
Route::get('/login', function () {
    return view('login');
});
```

### 3. 檢查認證狀態

在 Dashboard 載入時檢查 Token:

```javascript
// In Dashboard.vue
import { onMounted } from 'vue';
import axios from 'axios';

onMounted(async () => {
  const token = localStorage.getItem('api_token');
  if (!token) {
    window.location.href = '/login';
    return;
  }
  
  try {
    await axios.get('/api/user');
  } catch (err) {
    localStorage.removeItem('api_token');
    window.location.href = '/login';
  }
});
```

---

## 測試 API

### 使用 curl

```bash
# 取得 Token
TOKEN=$(curl -s -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | jq -r '.token')

# 使用 Token 呼叫 API
curl http://localhost/api/products \
  -H "Authorization: Bearer $TOKEN"
```

### 使用 Postman

1. 設定 Authorization Type 為 "Bearer Token"
2. 貼上 Token
3. 發送請求

---

## 常見問題

### Q: Token 過期了怎麼辦?

**A**: Sanctum Token 預設不會過期。如需設定過期時間:

```php
// config/sanctum.php
'expiration' => 60, // 60 分鐘
```

### Q: 如何登出?

**A**: 呼叫登出 API:

```javascript
await axios.post('/api/logout');
localStorage.removeItem('api_token');
window.location.href = '/login';
```

### Q: 如何在開發環境跳過認證?

**A**: 使用環境變數:

```php
// routes/api.php
$middleware = env('APP_ENV') === 'local' ? [] : ['auth:sanctum'];

Route::middleware($middleware)->group(function () {
    // ...
});
```

---

## 下一步

1. ✅ 建立測試使用者
2. ✅ 取得 Token
3. ✅ 在瀏覽器設定 Token
4. ✅ 測試 API 功能
5. 🔲 建立完整的登入/註冊頁面 (選填)

---

## 相關檔案

- [AuthController.php](file:///d:/web/prototype/app/Http/Controllers/Api/AuthController.php)
- [api.php](file:///d:/web/prototype/routes/api.php)
- [auth.php](file:///d:/web/prototype/config/auth.php)
