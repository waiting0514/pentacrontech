# 產品分類父層（非必填）功能實作規劃書 (已更新)

本規劃書旨在為系統導入「產品分類父層設定」功能。分類結構將升級為支援「無限級分類（或多級分類）」，且父層設定為**非必填（Optional）**。若分類未設定父層，則代表該分類為「一級分類（最上層）」。

根據您的決策與反饋，本方案已更新：
1. 採用**方案 B（阻擋刪除）**：若分類下仍有子分類，阻擋其刪除，提示使用者先移動或刪除子分類。
2. 規劃**取消父層的設定機制**：提供前端一鍵清空與預設「無（最上層）」選項，並由後端安全轉換為 `NULL` 寫入。

---

## 🛠️ 1. 資料庫結構設計 (Database Schema)

我們將在 `product_categories` 資料表中新增 `parent_id` 欄位，指向同一個資料表的 `id`，以建立自關聯。

### Migration 設計

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // 新增 parent_id 欄位，允許為 null (非必填)
            // 採用 restrict 刪除策略，雙重保障：當資料庫中仍有子分類指向此分類時，不允許刪除父分類
            $table->foreignId('parent_id')
                ->after('project_id')
                ->nullable()
                ->constrained('product_categories')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
```

---

## 🧠 2. 後端模型與關係 (Backend Model & Relations)

在 `ProductCategory.php` 中定義父子層級的 Eloquent 關聯，並加入防止「循環關聯」的安全防範。

### Model 修改: `app/Models/ProductCategory.php`

```php
protected $fillable = [
    'project_id',
    'parent_id', // 新增此欄位
    'name',
    'slug',
    'sort_order',
    'is_active',
    'is_system',
    'image_path',
];

/**
 * 取得父級分類 (BelongsTo)
 */
public function parent()
{
    return $this->belongsTo(self::class, 'parent_id');
}

/**
 * 取得子級分類 (HasMany)
 */
public function children()
{
    return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
}

/**
 * 遞迴取得所有子孫分類 (用於多級架構)
 */
public function allChildren()
{
    return $this->children()->with('allChildren');
}
```

---

## 🚦 3. 後端邏輯調整 (Controller Logic)

### A. 新增與更新時的「取消父層」與「防死鎖」邏輯
當使用者在前端取消選擇父分類時，傳入的 `parent_id` 會是空值（`null` 或空字串）。後端需將其轉化為資料庫支援的 `NULL`。

修改 `app/Http/Controllers/Api/ProductCategoryController.php`：

```php
public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'parent_id' => 'nullable|exists:product_categories,id',
        'name' => 'required|array',
        'name.zh-TW' => 'required|string|max:50',
        // ... 其他欄位
    ]);

    // 處理取消父層/無父層情況：若傳入為空，確保在寫入時設為 null
    if (empty($validated['parent_id'])) {
        $validated['parent_id'] = null;
    }

    $category = ProductCategory::create($validated);
    return $this->successResponse(new ProductCategoryResource($category), 'Category created', 201);
}

public function update(Request $request, ProductCategory $productCategory): JsonResponse
{
    $validated = $request->validate([
        'parent_id' => 'nullable|exists:product_categories,id',
        'name' => 'sometimes|array',
        // ... 其他欄位
    ]);

    // 1. 處理取消父層：如果 parent_id 傳入 null 或空字串，將欄位更新為 null 變回最上層
    if (array_key_exists('parent_id', $validated)) {
        $validated['parent_id'] = empty($validated['parent_id']) ? null : $validated['parent_id'];
    }

    // 2. 檢查防止循環關聯
    if (!empty($validated['parent_id'])) {
        $parentId = (int)$validated['parent_id'];
        
        // 不能選自己作為父層
        if ($parentId === $productCategory->id) {
            return $this->errorResponse('無法將自己設為父分類', 'INVALID_PARENT', 422);
        }
        
        // 不能選自己的子孫分類作為父層
        if ($this->isDescendantOf($parentId, $productCategory)) {
            return $this->errorResponse('無法將子分類設為父分類，這會造成循環關聯', 'LOOP_RELATION', 422);
        }
    }

    $productCategory->update($validated);
    return $this->successResponse(new ProductCategoryResource($productCategory), 'Category updated');
}

/**
 * 遞迴檢查某個分類 ID 是否是指定分類的子孫
 */
private function isDescendantOf(int $parentId, ProductCategory $category): bool
{
    $childrenIds = $category->children()->pluck('id')->toArray();
    
    if (in_array($parentId, $childrenIds)) {
        return true;
    }
    
    foreach ($category->children as $child) {
        if ($this->isDescendantOf($parentId, $child)) {
            return true;
        }
    }
    
    return false;
}
```

### B. 刪除時的「阻擋策略」（方案 B）
當準備刪除某分類時，後端需要先檢查是否有任何子分類依然依賴於它。若有，則回傳錯誤提示並阻擋操作。

```php
public function destroy(ProductCategory $productCategory): JsonResponse
{
    // 防止刪除系統內建分類
    if ($productCategory->isSystemCategory()) {
        return $this->errorResponse('系統分類無法刪除', 'SYSTEM_CATEGORY', 403);
    }

    // 【方案 B 阻擋】：檢查是否含有子分類
    $hasChildren = $productCategory->children()->exists();
    if ($hasChildren) {
        return $this->errorResponse(
            '該分類下仍有子分類，無法直接刪除。請先移動或刪除底下的子分類。',
            'HAS_CHILD_CATEGORIES',
            422
        );
    }

    // 刪除圖片
    if ($productCategory->image_path) {
        $this->imageService->deleteCategoryImage($productCategory->image_path);
    }

    // 處理該分類下的商品：轉移至預設分類 (Uncategorized)
    $defaultCategory = ProductCategory::getDefaultCategory($productCategory->project_id);
    $reassignedCount = 0;
    if ($defaultCategory) {
        $reassignedCount = $productCategory->products()->count();
        $productCategory->products()->update(['category_id' => $defaultCategory->id]);
    }

    $productCategory->delete();

    return $this->successResponse([
        'reassigned_products' => $reassignedCount,
    ], '分類已成功刪除');
}
```

---

## 🎨 4. 前端 UI/UX 設計 (取消父層之操作機制)

為了讓管理員可以**直覺地設定與取消父層**，我們在前端編輯視窗採取了雙重防護與最佳體驗設計：

### 設計 1：`el-tree-select` 一鍵清空屬性 (`clearable`)
Element Plus 的 `el-tree-select` 元件本身提供了 `clearable` 屬性。當選擇器內有值時，會自動在右側出現一個「叉叉 (X)」按鈕。使用者點擊即可立即將選擇值清空，此時綁定的 `form.parent_id` 會自動變為 `null`，送出後即代表**取消父層，成為最頂層分類**。

### 設計 2：預設「無父級分類（最上層）」下拉選項
為了讓操作更加明確，避免有些使用者找不到清空按鈕，我們會在樹狀資料的最頂層，主動加入一個**「無父級分類（最上層）」**的虛擬節點，供使用者直接點選。

#### 前端計算屬性實作：
```javascript
// 過濾與重組出可選的父分類清單，並在最上方插入「無父級分類」選項
const selectableCategories = computed(() => {
    // 1. 遞迴過濾與複製樹狀結構
    const filterTree = (nodes) => {
        return nodes
            .filter(node => {
                // 不能選擇當前編輯的分類本身
                if (isEditing.value && node.id === editingId.value) {
                    return false;
                }
                return true;
            })
            .map(node => {
                const mappedNode = {
                    value: node.id,
                    label: typeof node.name === 'object' ? (node.name?.['zh-TW'] || node.name?.en) : node.name,
                };
                
                if (node.children && node.children.length > 0) {
                    const filteredChildren = filterTree(node.children);
                    if (filteredChildren.length > 0) {
                        mappedNode.children = filteredChildren;
                    }
                }
                return mappedNode;
            });
    };
    
    // 2. 獲取過濾後的樹狀資料
    const filteredTree = filterTree(treeCategories.value);
    
    // 3. 在最頂端插入一個「無父級分類」的虛擬選項，點選後 value 為 null
    return [
        {
            value: null, // 或空字串
            label: '🏆 無父級分類（設為最上層）',
        },
        ...filteredTree
    ];
});
```

#### 前端 Form 欄位設計：
```html
<el-form-item label="父級分類">
  <el-tree-select
    v-model="form.parent_id"
    :data="selectableCategories"
    check-strictly
    :render-after-expand="false"
    placeholder="請選擇父級分類（留空代表最上層）"
    clearable
    filterable
    style="width: 100%;"
  >
    <template #default="{ data }">
      <!-- 若為虛擬首項，可以使用特殊樣式凸顯 -->
      <span v-if="data.value === null" style="color: #409eff; font-weight: bold;">
        {{ data.label }}
      </span>
      <span v-else>{{ data.label }}</span>
    </template>
  </el-tree-select>
</el-form-item>
```

---

## 💎 5. 總結

透過這次的設計調整：
1. **防呆刪除機制**：完美避開了誤刪父分類導致其下所有子分類被連鎖刪除的不良影響，前端與後端雙重阻擋，大幅提升系統的穩健度。
2. **直覺的取消父層設定**：使用者不管是點擊 **「一鍵清除 (X)」** 還是點選下拉清單首項的 **「🏆 無父級分類（設為最上層）」**，都能輕易且安全地取消父層設定。

這套方案將帶給管理員極為優良、清晰且放心的多層級分類管理體驗。
