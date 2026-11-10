<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { videoService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import { useLanguageStore } from '../../store/modules/language';

const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages.filter(l => l.is_active !== false));

const videos = ref([]);
const loading = ref(false);
const showDialog = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);
const activeTab = ref('');
const errors = ref({});

watch(languages, (langs) => {
    if (!activeTab.value && langs.length > 0) {
        activeTab.value = (langs.find(l => l.is_default) || langs[0]).code;
    }
}, { immediate: true });

const TEXT_FIELDS = ['title', 'description', 'meta_title', 'meta_description', 'meta_keywords'];

const ensureObject = (val) => {
    if (val && typeof val === 'object' && !Array.isArray(val)) return { ...val };
    return {};
};

const emptyForm = () => ({
    youtube_url: '',
    title: {},
    description: {},
    meta_title: {},
    meta_description: {},
    meta_keywords: {},
    slug: '',
    sort_order: 0,
    is_active: true,
});

const form = ref(emptyForm());

const displayTitle = (row) => row.title?.en || row.title?.['zh-TW'] || '(未命名)';

const fetchVideos = async () => {
    loading.value = true;
    try {
        const res = await videoService.list();
        const data = res.data.data;
        videos.value = Array.isArray(data) ? data : (data?.data || []);
    } catch (err) {
        ElMessage.error('載入影片失敗');
    } finally {
        loading.value = false;
    }
};

const openCreate = () => {
    isEditing.value = false;
    editingId.value = null;
    errors.value = {};
    form.value = emptyForm();
    form.value.sort_order = videos.value.length;
    showDialog.value = true;
};

const editVideo = (row) => {
    isEditing.value = true;
    editingId.value = row.id;
    errors.value = {};
    const f = emptyForm();
    f.youtube_url = row.youtube_url || '';
    TEXT_FIELDS.forEach(k => { f[k] = ensureObject(row[k]); });
    f.slug = row.slug || '';
    f.sort_order = row.sort_order ?? 0;
    f.is_active = !!row.is_active;
    form.value = f;
    showDialog.value = true;
};

const buildPayload = () => {
    const payload = {
        youtube_url: form.value.youtube_url.trim(),
        slug: form.value.slug ? form.value.slug.trim() : null,
        sort_order: form.value.sort_order ?? 0,
        is_active: !!form.value.is_active,
    };
    TEXT_FIELDS.forEach(k => { payload[k] = { ...form.value[k] }; });
    return payload;
};

const firstError = (key) => {
    const list = errors.value[key];
    return Array.isArray(list) ? list[0] : '';
};

const saveVideo = async () => {
    if (isSubmitting.value) return;
    errors.value = {};

    if (!form.value.youtube_url.trim()) {
        errors.value = { youtube_url: ['請輸入 YouTube 網址'] };
        return;
    }
    if (!(form.value.title.en || '').trim()) {
        errors.value = { 'title.en': ['Please enter an English title'] };
        activeTab.value = 'en';
        return;
    }

    isSubmitting.value = true;
    try {
        const payload = buildPayload();
        if (isEditing.value) {
            await videoService.update(editingId.value, payload);
            ElMessage.success('更新成功');
        } else {
            await videoService.create(payload);
            ElMessage.success('新增成功');
        }
        showDialog.value = false;
        fetchVideos();
    } catch (err) {
        const res = err.response;
        if (res?.status === 422) {
            errors.value = res.data?.errors || {};
            const msgs = Object.values(errors.value).flat();
            ElMessage.error(msgs[0] || res.data?.message || '資料驗證失敗');
        } else {
            ElMessage.error('儲存失敗：' + (res?.data?.message || err.message));
        }
    } finally {
        isSubmitting.value = false;
    }
};

const toggleActive = async (row, value) => {
    try {
        await videoService.update(row.id, {
            youtube_url: row.youtube_url,
            title: ensureObject(row.title),
            description: ensureObject(row.description),
            slug: row.slug,
            meta_title: ensureObject(row.meta_title),
            meta_description: ensureObject(row.meta_description),
            meta_keywords: ensureObject(row.meta_keywords),
            sort_order: row.sort_order,
            is_active: value,
        });
        row.is_active = value;
    } catch (err) {
        const msg = err.response?.data?.errors
            ? Object.values(err.response.data.errors).flat()[0]
            : err.response?.data?.message;
        ElMessage.error('狀態更新失敗' + (msg ? '：' + msg : ''));
    }
};

const deleteVideo = async (row) => {
    try {
        await ElMessageBox.confirm(`確定要刪除影片「${displayTitle(row)}」嗎？此操作無法復原。`, '警告', {
            confirmButtonText: '確定',
            cancelButtonText: '取消',
            type: 'warning',
        });
        await videoService.delete(row.id);
        ElMessage.success('刪除成功');
        fetchVideos();
    } catch (err) {
        if (err !== 'cancel') {
            ElMessage.error('刪除失敗');
        }
    }
};

onMounted(() => {
    languageStore.fetchLanguages();
    fetchVideos();
});
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>影片管理</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> 新增影片
      </el-button>
    </div>

    <el-table :data="videos" v-loading="loading" border stripe>
      <template #empty>
        <el-empty description="尚無影片，請點擊右上角「新增影片」" />
      </template>
      <el-table-column label="縮圖" width="160">
        <template #default="{ row }">
          <el-image
            v-if="row.thumbnail_url"
            :src="row.thumbnail_url"
            style="height: 54px; width: 96px;"
            fit="cover"
          >
            <template #error>
              <div style="display: flex; justify-content: center; align-items: center; height: 100%; background: #f5f7fa; color: #909399;">
                <el-icon><Picture /></el-icon>
              </div>
            </template>
          </el-image>
          <div v-else style="display: flex; justify-content: center; align-items: center; height: 54px; width: 96px; background: #f5f7fa; color: #909399;">
            <el-icon><Picture /></el-icon>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="名稱">
        <template #default="{ row }">
          <div style="font-weight: bold;">{{ displayTitle(row) }}</div>
          <div v-if="row.youtube_url" style="font-size: 12px; color: #909399;">{{ row.youtube_url }}</div>
        </template>
      </el-table-column>
      <el-table-column label="排序" prop="sort_order" width="90" align="center" />
      <el-table-column label="啟用" width="100" align="center">
        <template #default="{ row }">
          <el-switch :model-value="row.is_active" @change="(val) => toggleActive(row, val)" />
        </template>
      </el-table-column>
      <el-table-column label="操作" width="160" align="center">
        <template #default="{ row }">
          <el-button type="primary" link @click="editVideo(row)">編輯</el-button>
          <el-button type="danger" link @click="deleteVideo(row)">刪除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog
      v-model="showDialog"
      :title="isEditing ? '編輯影片' : '新增影片'"
      width="800px"
      destroy-on-close
    >
      <el-form :model="form" label-width="120px">
        <el-form-item
          label="YouTube 網址"
          required
          :error="firstError('youtube_url')"
        >
          <el-input v-model="form.youtube_url" placeholder="https://www.youtube.com/watch?v=..." clearable />
        </el-form-item>

        <el-tabs v-model="activeTab" type="border-card">
          <el-tab-pane v-for="lang in languages" :key="lang.code" :label="lang.name" :name="lang.code">
            <el-form-item
              :label="'名稱'"
              :required="lang.code === 'en'"
              :error="firstError(`title.${lang.code}`) || (lang.code === 'en' ? firstError('title') : '')"
            >
              <el-input v-model="form.title[lang.code]" />
            </el-form-item>
            <el-form-item label="簡介">
              <el-input v-model="form.description[lang.code]" type="textarea" :rows="3" />
            </el-form-item>
            <el-form-item label="Meta Title">
              <el-input v-model="form.meta_title[lang.code]" />
            </el-form-item>
            <el-form-item label="Meta Description">
              <el-input v-model="form.meta_description[lang.code]" type="textarea" :rows="2" />
            </el-form-item>
            <el-form-item label="Meta Keywords">
              <el-input v-model="form.meta_keywords[lang.code]" />
            </el-form-item>
          </el-tab-pane>
        </el-tabs>

        <el-form-item label="Slug" style="margin-top: 20px;" :error="firstError('slug')">
          <el-input v-model="form.slug" placeholder="留空將自動產生" />
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">留空時系統會依標題自動產生網址代稱</div>
        </el-form-item>
        <el-form-item label="排序" :error="firstError('sort_order')">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>
        <el-form-item label="啟用">
          <el-switch v-model="form.is_active" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false" :disabled="isSubmitting">取消</el-button>
        <el-button type="primary" @click="saveVideo" :loading="isSubmitting">儲存</el-button>
      </template>
    </el-dialog>
  </div>
</template>
