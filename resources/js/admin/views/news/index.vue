<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { newsService, newsCategoryService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import RichTextEditor from '../../components/RichTextEditor.vue';
import TranslatableInput from '../../components/TranslatableInput.vue';
import TranslatableRichText from '../../components/TranslatableRichText.vue';
import { useLanguageStore } from '../../store/modules/language';

const { proxy } = getCurrentInstance();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const newsList = ref([]);
const searchQuery = ref('');
const showDialog = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);
const categoryOptions = ref([]);

const form = ref({
    title: {},
    slug: {},
    content: {},
    image_url: '',
    category_id: null,
    meta_title: {},
    meta_description: {},
    meta_keywords: {},
    is_published: false,
    published_at: null,
});

const resetForm = () => {
    form.value = {
        title: {}, slug: {}, content: {}, image_url: '',
        category_id: null, meta_title: {}, meta_description: {}, meta_keywords: {},
        is_published: false, published_at: null,
    };
};

const fetchCategories = async () => {
    try {
        const response = await newsCategoryService.list();
        categoryOptions.value = response.data.data;
    } catch (err) {
        console.error('Fetch categories error:', err);
    }
};

const fetchNews = async () => {
    try {
        const params = searchQuery.value ? { search: searchQuery.value } : {};
        const response = await newsService.list(params);
        const resData = response.data.data;
        newsList.value = resData?.data || resData;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    }
};

const openCreate = () => {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
    showDialog.value = true;
};

const editNews = (news) => {
    isEditing.value = true;
    editingId.value = news.id;

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

    form.value = {
        title: ensureObject(news.title),
        slug: ensureObject(news.slug),
        content: ensureObject(news.content),
        image_url: news.image_url || '',
        category_id: news.category_id || null,
        meta_title: ensureObject(news.meta_title),
        meta_description: ensureObject(news.meta_description),
        meta_keywords: ensureObject(news.meta_keywords),
        is_published: news.is_published || false,
        published_at: news.published_at ? news.published_at.slice(0, 16) : null,
    };
    showDialog.value = true;
};

const saveNews = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    try {
        if (isEditing.value) {
            await newsService.update(editingId.value, form.value);
        } else {
            await newsService.create(form.value);
        }
        showDialog.value = false;
        ElMessage.success(proxy.$t('admin.messages.save_success'));
        fetchNews();
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.save_failed') + '：' + (err.response?.data?.message || err.message));
    } finally {
        isSubmitting.value = false;
    }
};

const togglePublish = async (news) => {
    try {
        await newsService.update(news.id, { ...news, is_published: !news.is_published });
        ElMessage.success(news.is_published ? proxy.$t('admin.news.unpublished_status') : proxy.$t('admin.news.published_status'));
        fetchNews();
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.operation_failed'));
    }
};

const deleteNews = async (id) => {
    try {
        await ElMessageBox.confirm(proxy.$t('admin.messages.news_delete_confirm'), proxy.$t('admin.messages.warning'), { type: 'warning' });
        await newsService.delete(id);
        ElMessage.success(proxy.$t('admin.messages.delete_success'));
        fetchNews();
    } catch (err) {
        if (err !== 'cancel') ElMessage.error(proxy.$t('admin.messages.delete_failed'));
    }
};

onMounted(() => { languageStore.fetchLanguages(); fetchNews(); fetchCategories(); });
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>{{ $t('admin.news.title') }}</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> {{ $t('admin.news.create') }}
      </el-button>
    </div>

    <el-input v-model="searchQuery" :placeholder="$t('admin.news.search_placeholder')" clearable @input="fetchNews" style="margin-bottom: 20px;">
      <template #prefix><el-icon><Search /></el-icon></template>
    </el-input>

    <el-table :data="newsList" border stripe>
      <el-table-column :label="$t('admin.news.news_title')">
        <template #default="{ row }">
          {{ typeof row.title === 'object' ? (row.title?.['zh-TW'] || row.title?.en) : row.title }}
        </template>
      </el-table-column>
      <el-table-column label="Slug" width="180">
        <template #default="{ row }">
          <el-text type="info" size="small" style="font-family: monospace;">
            {{ typeof row.slug === 'object' ? (row.slug?.['zh-TW'] || row.slug?.en) : row.slug }}
          </el-text>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.news.category')" width="140">
        <template #default="{ row }">
          <el-tag v-if="row.category" size="small">{{ row.category?.name?.['zh-TW'] || row.category?.name?.en || '-' }}</el-tag>
          <span v-else style="color: #909399;">-</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.news.publish_date')" width="140">
        <template #default="{ row }">
          {{ row.published_at ? new Date(row.published_at).toLocaleDateString('zh-TW') : '-' }}
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.news.status')" width="100">
        <template #default="{ row }">
          <el-tag :type="row.is_published ? 'success' : 'info'" size="small">
            {{ row.is_published ? $t('admin.news.published_status') : $t('admin.news.unpublished_status') }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.table.actions')" width="200" align="center">
        <template #default="{ row }">
          <el-button :type="row.is_published ? 'warning' : 'success'" link @click="togglePublish(row)">
            {{ row.is_published ? $t('admin.news.unpublish') : $t('admin.news.publish') }}
          </el-button>
          <el-button type="primary" link @click="editNews(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button type="danger" link @click="deleteNews(row.id)">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Dialog -->
    <el-dialog v-model="showDialog" :title="isEditing ? $t('admin.news.edit') : $t('admin.news.create')" width="900px" destroy-on-close>
      <el-form :model="form" label-width="130px">
        <el-form-item :label="$t('admin.news.news_title')" required>
          <TranslatableInput v-model="form.title" :languages="languages" :placeholder="$t('admin.news.news_title')" :maxlength="255" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.slug')">
          <TranslatableInput v-model="form.slug" :languages="languages" placeholder="news-2026" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.content')" required>
          <TranslatableRichText v-model="form.content" :languages="languages" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.cover_image')">
          <el-input v-model="form.image_url" placeholder="https://example.com/image.jpg" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.category')">
          <el-select v-model="form.category_id" :placeholder="$t('admin.news.no_category')" clearable>
            <el-option v-for="cat in categoryOptions" :key="cat.id" :label="cat.name?.['zh-TW'] || cat.name?.en" :value="cat.id" />
          </el-select>
        </el-form-item>

        <el-divider>{{ $t('admin.news.seo_settings') }}</el-divider>
        <el-form-item :label="$t('admin.news.seo_title')">
          <TranslatableInput v-model="form.meta_title" :languages="languages" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.seo_description')">
          <TranslatableInput v-model="form.meta_description" :languages="languages" type="textarea" :rows="2" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.seo_keywords')">
          <TranslatableInput v-model="form.meta_keywords" :languages="languages" />
        </el-form-item>

        <el-divider>{{ $t('admin.news.publish_settings') }}</el-divider>
        <el-form-item :label="$t('admin.news.publish_now')">
          <el-switch v-model="form.is_published" />
        </el-form-item>
        <el-form-item :label="$t('admin.news.publish_date')">
          <el-date-picker v-model="form.published_at" type="datetime" placeholder="選擇日期時間" value-format="YYYY-MM-DDTHH:mm" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false" :disabled="isSubmitting">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveNews" :loading="isSubmitting">
          {{ isEditing ? $t('admin.news.update') : $t('admin.actions.create') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style>
.ql-editor { min-height: 300px; }
</style>
