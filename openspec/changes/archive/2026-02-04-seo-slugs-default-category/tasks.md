## 1. Database Schema

- [x] 1.1 建立 Migration：新增 `is_system` 欄位至 `product_categories` 表
- [x] 1.2 建立 Migration：新增 `(project_id, slug)` 唯一索引至 `products` 表
- [x] 1.3 建立 Migration：新增 `(project_id, slug)` 唯一索引至 `product_categories` 表

## 2. Seeder

- [x] 2.1 建立 `DefaultCategorySeeder`：為每個專案建立預設分類 (slug=uncategorized, is_system=true)
- [x] 2.2 更新 `DatabaseSeeder` 確保 `DefaultCategorySeeder` 在其他分類之前執行

## 3. SlugService

- [x] 3.1 建立 `App\Services\SlugService` 類別
- [x] 3.2 實作 `generateSlug(string $name, string $table, ?int $projectId): string` 方法
- [x] 3.3 實作 `translateToSlug(string $name): string` 方法（AI 翻譯 + 降級邏輯）
- [x] 3.4 實作 `ensureUnique(string $slug, string $table, ?int $projectId, ?int $excludeId): string` 方法

## 4. Model Observers

- [x] 4.1 建立 `App\Observers\ProductObserver`：在 creating/updating 時自動生成 slug
- [x] 4.2 建立 `App\Observers\ProductCategoryObserver`：在 creating/updating 時自動生成 slug
- [x] 4.3 在 `AppServiceProvider` 註冊兩個 Observer

## 5. Model Updates

- [x] 5.1 更新 `ProductCategory` Model：新增 `is_system` 至 $fillable 和 $casts
- [x] 5.2 新增 `ProductCategory::getDefaultCategory(?int $projectId)` 靜態方法
- [x] 5.3 新增 `ProductCategory::isSystemCategory()` 方法

## 6. Controller Updates

- [x] 6.1 修改 `ProductCategoryController@destroy`：禁止刪除系統分類 (回傳 403)
- [x] 6.2 修改 `ProductCategoryController@destroy`：刪除前將產品重新指派至預設分類
- [x] 6.3 更新刪除 API 回應：包含重新指派的產品數量
- [x] 6.4 修改 `ProductCategoryController@update`：系統分類禁止修改 is_system 和 slug

## 7. Route Updates

- [x] 7.1 新增前台路由：`/products/{product:slug}` 使用 slug 綁定
- [x] 7.2 新增前台路由：`/category/{category:slug}` 使用 slug 綁定

## 8. Admin UI Updates

- [x] 8.1 更新 `ProductManager.vue`：新增 slug 輸入欄位（選填，留空自動產生）
- [x] 8.2 更新 `ProductManager.vue`：產品列表顯示 slug 欄位
- [x] 8.3 更新 `ProductController`：store/update 方法接受 slug 參數驗證

## 9. Testing

- [x] 9.1 測試：建立產品時自動生成 slug
- [x] 9.2 測試：建立分類時自動生成 slug
- [x] 9.3 測試：slug 衝突時自動附加數字後綴
- [x] 9.4 測試：禁止刪除系統分類
- [x] 9.5 測試：刪除分類時產品重新指派至預設分類
- [x] 9.6 測試：AI 服務不可用時降級處理
