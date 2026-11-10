<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { knowledgeArticleService, knowledgeCategoryService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import TranslatableInput from '../../components/TranslatableInput.vue';
import TranslatableRichText from '../../components/TranslatableRichText.vue';
import { useLanguageStore } from '../../store/modules/language';

const { proxy } = getCurrentInstance();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const articleList = ref([]);
const searchQuery = ref('');
const showDialog = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);
const categoryOptions = ref([]);

const schemaTypeOptions = [
    { value: 'article', label: 'Article（一般文章）' },
    { value: 'faq', label: 'FAQ（常見問答）' },
    { value: 'howto', label: 'How-To（操作指南）' },
    { value: 'tutorial', label: 'Tutorial（教學文章）' },
];

const form = ref({
    title: {},
    slug: {},
    excerpt: {},
    content: {},
    image_url: '',
    schema_type: 'article',
    category_id: null,
    meta_title: {},
    meta_description: {},
    meta_keywords: {},
    is_published: false,
    published_at: null,
});

const resetForm = () => {
    form.value = {
        title: {}, slug: {}, excerpt: {}, content: {}, image_url: '',
        schema_type: 'article', category_id: null,
        meta_title: {}, meta_description: {}, meta_keywords: {},
        is_published: false, published_at: null,
    };
};

const fetchCategories = async () => {
    try {
        const response = await knowledgeCategoryService.list();
        categoryOptions.value = response.data.data;
    } catch (err) {
        console.error('Fetch categories error:', err);
    }
};

const fetchArticles = async () => {
    try {
        const params = searchQuery.value ? { search: searchQuery.value } : {};
        const response = await knowledgeArticleService.list(params);
        const resData = response.data.data;
        articleList.value = resData?.data || resData;
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

const editArticle = (article) => {
    isEditing.value = true;
    editingId.value = article.id;

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

    form.value = {
        title: ensureObject(article.title),
        slug: ensureObject(article.slug),
        excerpt: ensureObject(article.excerpt),
        content: ensureObject(article.content),
        image_url: article.image_url || '',
        schema_type: article.schema_type || 'article',
        category_id: article.category_id || null,
        meta_title: ensureObject(article.meta_title),
        meta_description: ensureObject(article.meta_description),
        meta_keywords: ensureObject(article.meta_keywords),
        is_published: article.is_published || false,
        published_at: article.published_at ? article.published_at.slice(0, 16) : null,
    };
    showDialog.value = true;
};

const saveArticle = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    try {
        if (isEditing.value) {
            await knowledgeArticleService.update(editingId.value, form.value);
        } else {
            await knowledgeArticleService.create(form.value);
        }
        showDialog.value = false;
        ElMessage.success(proxy.$t('admin.messages.save_success'));
        fetchArticles();
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.save_failed') + '：' + (err.response?.data?.message || err.message));
    } finally {
        isSubmitting.value = false;
    }
};

const togglePublish = async (article) => {
    try {
        await knowledgeArticleService.update(article.id, { ...article, is_published: !article.is_published });
        ElMessage.success(article.is_published ? '已下架' : '已發布');
        fetchArticles();
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.operation_failed'));
    }
};

const deleteArticle = async (id) => {
    try {
        await ElMessageBox.confirm('確定要刪除此知識庫文章？', proxy.$t('admin.messages.warning'), { type: 'warning' });
        await knowledgeArticleService.delete(id);
        ElMessage.success(proxy.$t('admin.messages.delete_success'));
        fetchArticles();
    } catch (err) {
        if (err !== 'cancel') ElMessage.error(proxy.$t('admin.messages.delete_failed'));
    }
};

const schemaTypeLabel = (type) => {
    const map = { article: 'Article', faq: 'FAQ', howto: 'How-To', tutorial: 'Tutorial' };
    return map[type] || type;
};

const schemaTypeTagType = (type) => {
    const map = { article: '', faq: 'success', howto: 'warning', tutorial: 'info' };
    return map[type] || '';
};

onMounted(() => { languageStore.fetchLanguages(); fetchArticles(); fetchCategories(); });
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>知識庫管理</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> 新增文章
      </el-button>
    </div>

    <el-input v-model="searchQuery" placeholder="搜尋文章標題或內容..." clearable @input="fetchArticles" style="margin-bottom: 20px;">
      <template #prefix><el-icon><Search /></el-icon></template>
    </el-input>

    <el-table :data="articleList" border stripe>
      <el-table-column label="文章標題">
        <template #default="{ row }">
          {{ typeof row.title === 'object' ? (row.title?.en || row.title?.['zh-TW']) : row.title }}
        </template>
      </el-table-column>
      <el-table-column label="分類" width="130">
        <template #default="{ row }">
          <el-tag v-if="row.category" size="small">{{ row.category?.name?.en || row.category?.name?.['zh-TW'] || '-' }}</el-tag>
          <span v-else style="color: #909399;">-</span>
        </template>
      </el-table-column>
      <el-table-column label="類型" width="110">
        <template #default="{ row }">
          <el-tag :type="schemaTypeTagType(row.schema_type)" size="small">{{ schemaTypeLabel(row.schema_type) }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="閱讀時間" width="100" align="center">
        <template #default="{ row }">
          <span v-if="row.reading_time" style="color: #606266; font-size: 12px;">{{ row.reading_time }} min</span>
          <span v-else style="color: #c0c4cc;">-</span>
        </template>
      </el-table-column>
      <el-table-column label="狀態" width="90">
        <template #default="{ row }">
          <el-tag :type="row.is_published ? 'success' : 'info'" size="small">
            {{ row.is_published ? '已發布' : '草稿' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.table.actions')" width="200" align="center">
        <template #default="{ row }">
          <el-button :type="row.is_published ? 'warning' : 'success'" link @click="togglePublish(row)">
            {{ row.is_published ? '下架' : '發布' }}
          </el-button>
          <el-button type="primary" link @click="editArticle(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button type="danger" link @click="deleteArticle(row.id)">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Dialog -->
    <el-dialog v-model="showDialog" :title="isEditing ? '編輯知識庫文章' : '新增知識庫文章'" width="900px" destroy-on-close>
      <el-form :model="form" label-width="130px">
        <el-form-item label="文章標題" required>
          <TranslatableInput v-model="form.title" :languages="languages" placeholder="文章標題" :maxlength="255" />
        </el-form-item>
        <el-form-item label="URL Slug">
          <TranslatableInput v-model="form.slug" :languages="languages" placeholder="what-is-seo" />
        </el-form-item>
        <el-form-item label="摘要 Excerpt">
          <TranslatableInput v-model="form.excerpt" :languages="languages" type="textarea" :rows="3"
            placeholder="簡短說明文章重點，用於列表卡片與 SEO meta description 備用" />
        </el-form-item>
        <el-form-item label="文章內容" required>
          <TranslatableRichText v-model="form.content" :languages="languages" />
        </el-form-item>
        <el-form-item label="封面圖">
          <el-input v-model="form.image_url" placeholder="https://example.com/image.jpg" />
        </el-form-item>
        <el-form-item label="Schema 類型">
          <el-select v-model="form.schema_type" style="width: 100%;">
            <el-option v-for="opt in schemaTypeOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
          </el-select>
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">
            FAQ → Google FAQ 富格式；How-To → 操作步驟；Tutorial / Article → TechArticle
          </div>
        </el-form-item>
        <el-form-item label="分類">
          <el-select v-model="form.category_id" placeholder="選擇分類" clearable>
            <el-option v-for="cat in categoryOptions" :key="cat.id" :label="cat.name?.en || cat.name?.['zh-TW']" :value="cat.id" />
          </el-select>
        </el-form-item>

        <el-divider>SEO 設定</el-divider>
        <el-form-item label="SEO 標題">
          <TranslatableInput v-model="form.meta_title" :languages="languages" />
        </el-form-item>
        <el-form-item label="SEO 描述">
          <TranslatableInput v-model="form.meta_description" :languages="languages" type="textarea" :rows="2" />
        </el-form-item>
        <el-form-item label="SEO 關鍵字">
          <TranslatableInput v-model="form.meta_keywords" :languages="languages" />
        </el-form-item>

        <el-divider>發布設定</el-divider>
        <el-form-item label="立即發布">
          <el-switch v-model="form.is_published" />
        </el-form-item>
        <el-form-item label="發布時間">
          <el-date-picker v-model="form.published_at" type="datetime" placeholder="選擇日期時間" value-format="YYYY-MM-DDTHH:mm" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false" :disabled="isSubmitting">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveArticle" :loading="isSubmitting">
          {{ isEditing ? '更新文章' : $t('admin.actions.create') }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<style>
.ql-editor { min-height: 300px; }
</style>
