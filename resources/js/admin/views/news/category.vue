<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { newsCategoryService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import TranslatableInput from '../../components/TranslatableInput.vue';
import { useLanguageStore } from '../../store/modules/language';

const { proxy } = getCurrentInstance();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const categories = ref([]);
const showDialog = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);

const form = ref({ name: {}, slug: {}, sort_order: 0, is_active: true });

const fetchCategories = async () => {
    try {
        const response = await newsCategoryService.list();
        categories.value = response.data.data;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    }
};

const openCreate = () => {
    isEditing.value = false;
    editingId.value = null;
    form.value = { name: {}, slug: {}, sort_order: categories.value.length, is_active: true };
    showDialog.value = true;
};

const ensureObject = (val) => {
    if (val && typeof val === 'object' && !Array.isArray(val)) return { ...val };
    if (typeof val === 'string') {
        try {
            const parsed = JSON.parse(val);
            return typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return { en: val };
        }
    }
    return {};
};

const editCategory = (category) => {
    isEditing.value = true;
    editingId.value = category.id;
    form.value = {
        name: ensureObject(category.name),
        slug: ensureObject(category.slug),
        sort_order: category.sort_order || 0,
        is_active: category.is_active,
    };
    showDialog.value = true;
};

const saveCategory = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    try {
        if (isEditing.value) {
            await newsCategoryService.update(editingId.value, form.value);
        } else {
            await newsCategoryService.create(form.value);
        }
        showDialog.value = false;
        ElMessage.success(proxy.$t('admin.messages.save_success'));
        fetchCategories();
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.save_failed') + '：' + (err.response?.data?.message || err.message));
    } finally {
        isSubmitting.value = false;
    }
};

const deleteCategory = async (id) => {
    try {
        await ElMessageBox.confirm(proxy.$t('admin.messages.category_delete_confirm'), proxy.$t('admin.messages.warning'), { type: 'warning' });
        await newsCategoryService.delete(id);
        ElMessage.success(proxy.$t('admin.messages.delete_success'));
        fetchCategories();
    } catch (err) {
        if (err !== 'cancel') ElMessage.error(proxy.$t('admin.messages.delete_failed'));
    }
};

onMounted(() => { languageStore.fetchLanguages(); fetchCategories(); });
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>{{ $t('admin.categories.news_categories') }}</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> {{ $t('admin.categories.create') }}
      </el-button>
    </div>

    <el-table :data="categories" border stripe>
      <el-table-column :label="$t('admin.categories.name')">
        <template #default="{ row }">
          {{ typeof row.name === 'object' ? (row.name?.en || row.name?.['zh-TW']) : row.name }}
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.categories.url_slug')">
        <template #default="{ row }">
          <el-text type="info" size="small" style="font-family: monospace;">
            {{ typeof row.slug === 'object' ? (row.slug?.en || row.slug?.['zh-TW']) : row.slug }}
          </el-text>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.categories.sort_order')" prop="sort_order" width="100" />
      <el-table-column :label="$t('admin.table.actions')" width="160" align="center">
        <template #default="{ row }">
          <el-button type="primary" link @click="editCategory(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button type="danger" link @click="deleteCategory(row.id)">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="showDialog" :title="isEditing ? $t('admin.categories.edit') : $t('admin.categories.create')" width="600px" destroy-on-close>
      <el-form :model="form" label-width="120px">
        <el-form-item :label="$t('admin.categories.name')" required>
          <TranslatableInput v-model="form.name" :languages="languages" :placeholder="$t('admin.categories.name')" :maxlength="50" />
        </el-form-item>
        <el-form-item :label="$t('admin.categories.url_slug')">
          <TranslatableInput v-model="form.slug" :languages="languages" placeholder="news-category" />
        </el-form-item>
        <el-form-item :label="$t('admin.categories.sort_order')">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false" :disabled="isSubmitting">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveCategory" :loading="isSubmitting">{{ $t('admin.actions.save') }}</el-button>
      </template>
    </el-dialog>
  </div>
</template>
