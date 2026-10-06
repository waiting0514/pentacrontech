## Context

現有 Laravel 12 + Vue 3 後台系統，使用 Sanctum token 認證，單一管理員帳號。後台已重構為 Vue Pure Admin 架構（Element Plus、vue-router、Pinia）。需要在此基礎上加入 RBAC 權限系統。

核心策略：**Check Permissions, Assign Roles**——程式碼中一律檢查 permission，管理介面僅操作 role 指派。

## Goals / Non-Goals

**Goals:**
- 導入 spatie/laravel-permission，定義細粒度 permission 清單
- 三種預設角色（super-admin、manager、editor）透過 Seeder 建立
- 所有 Controller/Policy 改為 `$this->authorize('permission.name')` 檢查
- super-admin 透過 `Gate::before` 繞過所有檢查
- 前端依 permission 陣列控制 UI 元素顯示
- Manager 可在後台管理 editor 帳號（CRUD + 啟用/停用）

**Non-Goals:**
- 不提供前台角色 CRUD（角色固定在 Seeder）
- 不提供前台權限 CRUD（權限固定在 Seeder）
- 不實作多租戶或 team-based permission

## Decisions

### 1. Permission 命名規範

採用 `resource.action` 格式：

```
product.view, product.create, product.update, product.delete
news.view, news.create, news.update, news.delete
banner.manage
ai.use
user.manage
report.view_cost
system.manage
```

### 2. 角色權限對應

| Role | Permissions |
|------|------------|
| super-admin | `*` (Gate::before bypass) |
| manager | `product.*`, `news.*`, `banner.manage`, `ai.use`, `user.manage`, `report.view_cost` |
| editor | `product.view`, `product.create`, `product.update`, `news.*`, `banner.manage`, `ai.use` |

差異：editor 無 `product.delete`、`user.manage`、`report.view_cost`、`system.manage`。

### 3. Gate::before 實作

```php
// AuthServiceProvider or AppServiceProvider
Gate::before(function ($user, $ability) {
    return $user->hasRole('super-admin') ? true : null;
});
```

### 4. Controller 權限檢查方式

所有 controller 使用 `$this->authorize('permission.name')` 或 `$this->middleware('can:permission.name')`：

```php
// ProductController
public function destroy(Product $product) {
    $this->authorize('product.delete');
    $product->delete();
}
```

### 5. is_active 中間件

建立 `EnsureUserIsActive` middleware，在 auth:sanctum 之後檢查：

```php
if (!$request->user()->is_active) {
    return response()->json(['message' => '帳號已停用'], 403);
}
```

### 6. User API Resource 回傳格式

```json
{
  "id": 1,
  "name": "Staff A",
  "email": "staff@example.com",
  "role": "editor",
  "permissions": ["product.view", "product.create", "product.update", "news.view", ...],
  "is_active": true
}
```

### 7. 前端 usePermission composable

```js
const { can, is } = usePermission();
// can('product.delete') → boolean
// is('manager') → boolean
```

從 Pinia user store 的 permissions 陣列中查詢。super-admin 的 `can()` 永遠回傳 true。

### 8. 前端路由權限

router meta 加入 `permission` 欄位，在 beforeEach 守衛中檢查：

```js
{
  path: 'users',
  meta: { title: '使用者管理', icon: 'User', permission: 'user.manage' },
}
```

Sidebar 依 permission 過濾選單項目。

### 9. UserController API

```
GET    /api/users              → 列表（需 user.manage）
POST   /api/users              → 建立（需 user.manage，自動指派 editor）
PUT    /api/users/{id}         → 更新（需 user.manage）
PUT    /api/users/{id}/toggle  → 啟用/停用（需 user.manage）
DELETE /api/users/{id}         → 刪除（僅 super-admin）
```

Manager 建立使用者時預設角色為 editor，可選擇指派為 manager 或 editor。

## Risks / Trade-offs

- **現有使用者遷移** → Seeder 需將現有使用者指派角色。seed 時檢查是否已存在使用者。
- **權限快取** → spatie 有內建 permission cache，`php artisan permission:cache-reset` 清除。部署時需執行。
- **前端權限可繞過** → 前端 permission 僅為 UI 控制，真正的安全在後端 authorize。
- **Token 不含權限** → 每次 API 請求由 middleware 從 DB 檢查。spatie 有 cache 機制減輕負擔。
