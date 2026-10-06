<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { productCategoryService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import TranslatableInput from '../../components/TranslatableInput.vue';
import { useLanguageStore } from '../../store/modules/language';
import { Plus, Delete } from '@element-plus/icons-vue';

const { proxy } = getCurrentInstance();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const categories = ref([]);
const showDialog = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);

const categoryImageFile = ref(null);
const categoryImagePreview = ref(null);
const removeImageFlag = ref(false);
const fileInputRef = ref(null);

const form = ref({ parent_id: null, name: {}, slug: {}, sort_order: 0, is_active: true });

const fetchCategories = async () => {
    try {
        const response = await productCategoryService.list();
        categories.value = response.data.data;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    }
};

const openCreate = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = { parent_id: null, name: {}, slug: {}, sort_order: categories.value.length, is_active: true };
    
    categoryImageFile.value = null;
    if (categoryImagePreview.value && !categoryImagePreview.value.startsWith('http')) {
        URL.revokeObjectURL(categoryImagePreview.value);
    }
    categoryImagePreview.value = null;
    removeImageFlag.value = false;
    
    showDialog.value = true;
};

const ensureObject = (val) => {
    if (val && typeof val === 'object' && !Array.isArray(val)) return { ...val };
    if (typeof val === 'string') {
        try {
            const parsed = JSON.parse(val);
            return typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return { 'zh-TW': val };
        }
    }
    return {};
};

const editCategory = (category) => {
    isEditing.value = true;
    editingId.value = category.id;
    form.value = {
        parent_id: category.parent_id || null,
        name: ensureObject(category.name),
        slug: ensureObject(category.slug),
        sort_order: category.sort_order || 0,
        is_active: category.is_active,
    };
    
    categoryImageFile.value = null;
    if (categoryImagePreview.value && !categoryImagePreview.value.startsWith('http')) {
        URL.revokeObjectURL(categoryImagePreview.value);
    }
    categoryImagePreview.value = category.image_url || null;
    removeImageFlag.value = false;
    
    showDialog.value = true;
};

// 將平鋪的一維陣列重組為多維樹狀結構
const buildTree = (list) => {
    const map = {};
    const tree = [];
    
    // 初始化 map
    list.forEach(item => {
        map[item.id] = { ...item, children: [] };
    });
    
    list.forEach(item => {
        const node = map[item.id];
        if (item.parent_id) {
            if (map[item.parent_id]) {
                map[item.parent_id].children.push(node);
            } else {
                // 如果父分類不在列表中，當作最頂層
                tree.push(node);
            }
        } else {
            tree.push(node);
        }
    });
    
    // 遞迴排序
    const sortTree = (nodes) => {
        nodes.sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id));
        nodes.forEach(node => {
            if (node.children && node.children.length > 0) {
                sortTree(node.children);
            } else {
                delete node.children; // 沒有子節點時，刪除 children 屬性，避免 Element 顯示摺疊小箭頭
            }
        });
    };
    sortTree(tree);
    return tree;
};

// 樹狀表格數據
const treeCategories = computed(() => {
    return buildTree(categories.value);
});

// 過濾可選擇為父層的分類清單（排除自己及其子樹，防止自關聯與循環）
const selectableCategories = computed(() => {
    const filterTree = (nodes) => {
        return nodes
            .filter(node => {
                // 不能選擇自己
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
    
    const filteredTree = filterTree(treeCategories.value);
    
    // 在最上方加上「無父級分類」選項，點選後為 null (取消父層)
    return [
        {
            value: null,
            label: '🏆 無父級分類（設為最上層）',
        },
        ...filteredTree
    ];
});

const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB

const onFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > MAX_IMAGE_SIZE) {
        ElMessage.warning('圖片大小不能超過 2MB');
        return;
    }
    categoryImageFile.value = file;
    if (categoryImagePreview.value && !categoryImagePreview.value.startsWith('http')) {
        URL.revokeObjectURL(categoryImagePreview.value);
    }
    categoryImagePreview.value = URL.createObjectURL(file);
    removeImageFlag.value = false;
    event.target.value = '';
};

const removeImage = () => {
    categoryImageFile.value = null;
    if (categoryImagePreview.value && !categoryImagePreview.value.startsWith('http')) {
        URL.revokeObjectURL(categoryImagePreview.value);
    }
    categoryImagePreview.value = null;
    removeImageFlag.value = true;
};

const saveCategory = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    try {
        const formData = new FormData();
        
        // Append simple fields
        formData.append('sort_order', form.value.sort_order || 0);
        formData.append('is_active', form.value.is_active ? 1 : 0);
        
        // Append parent_id (若為空則傳空字串，後端會轉成 null)
        if (form.value.parent_id !== undefined && form.value.parent_id !== null) {
            formData.append('parent_id', form.value.parent_id);
        } else {
            formData.append('parent_id', '');
        }

        // Append multilingual fields
        const langFields = ['name', 'slug'];
        langFields.forEach(field => {
            const values = form.value[field] || {};
            Object.keys(values).forEach(locale => {
                if (values[locale] !== undefined && values[locale] !== null) {
                    formData.append(`${field}[${locale}]`, values[locale]);
                }
            });
        });

        // Append image file or remove image flag
        if (categoryImageFile.value) {
            formData.append('image', categoryImageFile.value);
        } else if (removeImageFlag.value) {
            formData.append('remove_image', 1);
        }

        if (isEditing.value) {
            await productCategoryService.update(editingId.value, formData);
        } else {
            await productCategoryService.create(formData);
        }

        showDialog.value = false;
        ElMessage.success(proxy.$t('admin.messages.save_success'));
        fetchCategories();
        
        // Clear preview
        if (categoryImagePreview.value && !categoryImagePreview.value.startsWith('http')) {
            URL.revokeObjectURL(categoryImagePreview.value);
        }
        categoryImagePreview.value = null;
        categoryImageFile.value = null;
        removeImageFlag.value = false;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.save_failed') + '：' + (err.response?.data?.message || err.message));
    } finally {
        isSubmitting.value = false;
    }
};

const deleteCategory = async (id) => {
    try {
        await ElMessageBox.confirm(proxy.$t('admin.messages.category_delete_confirm'), proxy.$t('admin.messages.warning'), { type: 'warning' });
        await productCategoryService.delete(id);
        ElMessage.success(proxy.$t('admin.messages.delete_success'));
        fetchCategories();
    } catch (err) {
        if (err !== 'cancel') {
            const errorMsg = err.response?.data?.message || proxy.$t('admin.messages.delete_failed');
            ElMessage.error(errorMsg);
        }
    }
};

onMounted(() => { languageStore.fetchLanguages(); fetchCategories(); });
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>{{ $t('admin.categories.product_categories') }}</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> {{ $t('admin.categories.create') }}
      </el-button>
    </div>

    <!-- 升級為樹狀表格，並以分類名稱作為首欄以獲得完美的箭頭展示 -->
    <el-table 
      :data="treeCategories" 
      row-key="id" 
      border 
      stripe 
      default-expand-all 
      :tree-props="{ children: 'children', hasChildren: 'hasChildren' }">
      
      <el-table-column :label="$t('admin.categories.name')">
        <template #default="{ row }">
          <span style="font-weight: 500;">
            {{ typeof row.name === 'object' ? (row.name?.['zh-TW'] || row.name?.en) : row.name }}
          </span>
          <el-tag v-if="!row.parent_id" size="small" type="success" style="margin-left: 8px;">主分類</el-tag>
          <el-tag v-else size="small" type="info" style="margin-left: 8px;">子分類</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="縮圖" width="100" align="center">
        <template #default="{ row }">
          <el-image v-if="row.image_url" :src="row.image_url" style="width: 40px; height: 40px; border-radius: 4px; border: 1px solid #dcdfe6;" fit="cover" />
          <el-text v-else type="info">-</el-text>
        </template>
      </el-table-column>

      <el-table-column :label="$t('admin.categories.url_slug')">
        <template #default="{ row }">
          <el-text type="info" size="small" style="font-family: monospace;">
            {{ typeof row.slug === 'object' ? (row.slug?.['zh-TW'] || row.slug?.en) : row.slug }}
          </el-text>
        </template>
      </el-table-column>
      
      <el-table-column :label="$t('admin.categories.sort_order')" prop="sort_order" width="100" align="center" />
      
      <el-table-column :label="$t('admin.table.actions')" width="160" align="center">
        <template #default="{ row }">
          <el-button type="primary" link @click="editCategory(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button type="danger" link @click="deleteCategory(row.id)" :disabled="row.is_system">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="showDialog" :title="isEditing ? $t('admin.categories.edit') : $t('admin.categories.create')" width="600px" destroy-on-close>
      <el-form :model="form" label-width="120px">
        <!-- 新增父級分類選擇器，支援階層與一鍵清空 -->
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
              <span v-if="data.value === null" style="color: #409eff; font-weight: bold;">
                {{ data.label }}
              </span>
              <span v-else>{{ data.label }}</span>
            </template>
          </el-tree-select>
        </el-form-item>

        <el-form-item :label="$t('admin.categories.name')" required>
          <TranslatableInput v-model="form.name" :languages="languages" :placeholder="$t('admin.categories.name')" :maxlength="50" />
        </el-form-item>
        <el-form-item :label="$t('admin.categories.url_slug')">
          <TranslatableInput v-model="form.slug" :languages="languages" placeholder="electronics" />
        </el-form-item>
        <el-form-item :label="$t('admin.categories.sort_order')">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>
        <el-form-item label="分類縮圖">
          <div style="display: flex; align-items: center; gap: 15px;">
            <div v-if="categoryImagePreview" style="position: relative; width: 100px; height: 100px;">
              <el-image :src="categoryImagePreview" style="width: 100px; height: 100px; border-radius: 8px; border: 1px solid #dcdfe6;" fit="cover" />
              <el-button type="danger" :icon="Delete" circle size="small"
                style="position: absolute; top: -10px; right: -10px;" @click="removeImage" />
            </div>
            <div v-else
              style="border: 2px dashed #dcdfe6; border-radius: 8px; width: 100px; height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center; cursor: pointer; background: #fafafa;"
              @click="fileInputRef?.click()">
              <el-icon :size="20" color="#909399"><Plus /></el-icon>
              <span style="font-size: 12px; color: #909399; margin-top: 4px;">選擇圖片</span>
            </div>
            <input ref="fileInputRef" type="file" accept="image/jpeg,image/png,image/webp" style="display: none;" @change="onFileSelect" />
            <div style="font-size: 12px; color: #909399; line-height: 1.4;">
              建議尺寸：600x600px<br>
              支援格式：JPG, PNG, WEBP<br>
              大小不超過 2MB
            </div>
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false" :disabled="isSubmitting">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveCategory" :loading="isSubmitting">{{ $t('admin.actions.save') }}</el-button>
      </template>
    </el-dialog>
  </div>
</template>
