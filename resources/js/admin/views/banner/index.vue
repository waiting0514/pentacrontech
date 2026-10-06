<script setup>
import { ref, computed, watch, onMounted, getCurrentInstance } from 'vue';
import { bannerService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import TranslatableInput from '../../components/TranslatableInput.vue';
import { useLanguageStore } from '../../store/modules/language';

const { proxy } = getCurrentInstance();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const banners = ref([]);
const showDialog = ref(false);
const isEditing = ref(false);
const editingBanner = ref(null);
const isSubmitting = ref(false);
const activeTab = ref('');

watch(languages, (langs) => {
    if (!activeTab.value && langs.length > 0) {
        activeTab.value = langs[0].code;
    }
}, { immediate: true });

const form = ref({
    title: {},
    link_url: {},
    sort_order: 0,
    is_active: false
});

// Files and Previews organized by locale
const pcFiles = ref({});
const mobileFiles = ref({});
const pcPreviews = ref({});
const mobilePreviews = ref({});

const fetchBanners = async () => {
    try {
        const res = await bannerService.list();
        banners.value = res.data.data;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    }
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

const openCreate = () => {
    isEditing.value = false;
    editingBanner.value = null;
    form.value = {
        title: {},
        link_url: {},
        sort_order: banners.value.length,
        is_active: true
    };
    pcFiles.value = {};
    mobileFiles.value = {};
    pcPreviews.value = {};
    mobilePreviews.value = {};
    showDialog.value = true;
};

const editBanner = (banner) => {
    isEditing.value = true;
    editingBanner.value = banner;
    form.value = {
        title: ensureObject(banner.title),
        link_url: ensureObject(banner.link_url),
        sort_order: banner.sort_order,
        is_active: banner.is_active,
    };
    pcFiles.value = {};
    mobileFiles.value = {};
    pcPreviews.value = {};
    mobilePreviews.value = {};
    showDialog.value = true;
};

const onFileSelect = (e, type, locale) => {
    const file = e.target.files[0];
    if (!file) return;
    
    if (type === 'pc') {
        pcFiles.value[locale] = file;
        pcPreviews.value[locale] = URL.createObjectURL(file);
    } else {
        mobileFiles.value[locale] = file;
        mobilePreviews.value[locale] = URL.createObjectURL(file);
    }
};

const getImageUrl = (type, locale) => {
    // 1. Check local preview first
    if (type === 'pc' && pcPreviews.value[locale]) return pcPreviews.value[locale];
    if (type === 'mobile' && mobilePreviews.value[locale]) return mobilePreviews.value[locale];
    
    // 2. Check existing image from banner data
    if (isEditing.value && editingBanner.value) {
        const path = type === 'pc' 
            ? editingBanner.value.image_pc?.[locale] 
            : editingBanner.value.image_mobile?.[locale];
        if (path) return `/storage/${path}`;
    }
    
    return null;
};

const saveBanner = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    try {
        const formData = new FormData();
        
        // Append translatable fields as objects/JSON
        Object.keys(form.value.title).forEach(lang => {
            formData.append(`title[${lang}]`, form.value.title[lang] || '');
        });
        Object.keys(form.value.link_url).forEach(lang => {
            formData.append(`link_url[${lang}]`, form.value.link_url[lang] || '');
        });
        
        formData.append('sort_order', form.value.sort_order);
        formData.append('is_active', form.value.is_active ? '1' : '0');

        // Append files
        Object.keys(pcFiles.value).forEach(lang => {
            formData.append(`image_pc[${lang}]`, pcFiles.value[lang]);
        });
        Object.keys(mobileFiles.value).forEach(lang => {
            formData.append(`image_mobile[${lang}]`, mobileFiles.value[lang]);
        });

        if (isEditing.value) {
            formData.append('_method', 'PUT');
            await bannerService.update(editingBanner.value.id, formData);
            ElMessage.success(proxy.$t('admin.messages.update_success'));
        } else {
            await bannerService.create(formData);
            ElMessage.success(proxy.$t('admin.messages.save_success'));
        }

        showDialog.value = false;
        fetchBanners();
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.save_failed') + '：' + (err.response?.data?.message || err.message));
    } finally {
        isSubmitting.value = false;
    }
};

const updateSort = async (banner, value) => {
    const sortOrder = parseInt(value);
    if (sortOrder === banner.sort_order) return;
    try {
        await bannerService.updateSort(banner.id, sortOrder);
        banner.sort_order = sortOrder;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.sort_update_failed'));
    }
};

const toggleActive = async (banner) => {
    try {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('is_active', banner.is_active ? '0' : '1');
        
        // Need to send required fields for validation
        Object.keys(banner.title).forEach(lang => {
            formData.append(`title[${lang}]`, banner.title[lang]);
        });

        await bannerService.update(banner.id, formData);
        banner.is_active = !banner.is_active;
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.status_update_failed'));
    }
};

const deleteBanner = async (id) => {
    try {
        await ElMessageBox.confirm(proxy.$t('admin.messages.banner_delete_confirm'), proxy.$t('admin.messages.warning'), {
            confirmButtonText: proxy.$t('admin.actions.confirm'),
            cancelButtonText: proxy.$t('admin.actions.cancel'),
            type: 'warning',
        });
        await bannerService.delete(id);
        ElMessage.success(proxy.$t('admin.messages.delete_success'));
        fetchBanners();
    } catch (err) {
        if (err !== 'cancel') {
            ElMessage.error(proxy.$t('admin.messages.delete_failed'));
        }
    }
};

onMounted(() => {
    languageStore.fetchLanguages();
    fetchBanners();
});
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>{{ $t('admin.banners.title') }}</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> {{ $t('admin.banners.create') }}
      </el-button>
    </div>

    <el-table :data="banners" border stripe>
      <el-table-column :label="$t('admin.banners.image')" width="200">
        <template #default="{ row }">
          <el-image
            v-if="row.image_pc_url"
            :src="row.image_pc_url"
            style="height: 48px; width: 120px;"
            fit="cover"
          >
            <template #error>
              <div style="display: flex; justify-content: center; align-items: center; height: 100%; background: #f5f7fa; color: #909399;">
                <el-icon><Picture /></el-icon>
              </div>
            </template>
          </el-image>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.table.title')">
        <template #default="{ row }">
          <div style="font-weight: bold;">{{ row.title?.['zh-TW'] || row.title?.en }}</div>
          <div v-if="row.link_url?.['zh-TW'] || row.link_url?.en" style="font-size: 12px; color: #909399;">
            {{ row.link_url?.['zh-TW'] || row.link_url?.en }}
          </div>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.banners.sort_order')" width="100">
        <template #default="{ row }">
          <el-input-number
            :model-value="row.sort_order"
            size="small"
            :min="0"
            controls-position="right"
            @change="(val) => updateSort(row, val)"
          />
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.banners.status')" width="100">
        <template #default="{ row }">
          <el-switch :model-value="row.is_active" @change="toggleActive(row)" />
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.table.actions')" width="160" align="center">
        <template #default="{ row }">
          <el-button type="primary" link @click="editBanner(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button type="danger" link @click="deleteBanner(row.id)">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Dialog -->
    <el-dialog
      v-model="showDialog"
      :title="isEditing ? $t('admin.banners.edit') : $t('admin.banners.create')"
      width="800px"
      destroy-on-close
    >
      <el-form :model="form" label-width="120px">
        <el-form-item :label="$t('admin.banners.alt_text')" required>
          <TranslatableInput v-model="form.title" :languages="languages" />
        </el-form-item>
        <el-form-item :label="$t('admin.banners.link_url')">
          <TranslatableInput v-model="form.link_url" :languages="languages" placeholder="https://..." />
        </el-form-item>
        
        <el-divider>{{ $t('admin.banners.image') }}</el-divider>
        
        <el-tabs v-model="activeTab" type="border-card">
          <el-tab-pane v-for="lang in languages" :key="lang.code" :label="lang.name" :name="lang.code">
            <div style="padding: 10px;">
              <el-row :gutter="20">
                <el-col :span="12">
                  <div style="margin-bottom: 10px; font-weight: bold;">{{ $t('admin.banners.pc_image') }}</div>
                  <input type="file" accept="image/jpeg,image/png,image/webp" @change="(e) => onFileSelect(e, 'pc', lang.code)" />
                  <div style="margin-top: 6px; font-size: 12px; color: #909399; line-height: 1.6;">
                    {{ $t('admin.banners.pc_image_hint') }}<br>
                    {{ $t('admin.banners.image_format_hint') }}
                  </div>
                  <div style="margin-top: 8px; border: 1px dashed #dcdfe6; height: 120px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                    <el-image
                      v-if="getImageUrl('pc', lang.code)"
                      :src="getImageUrl('pc', lang.code)"
                      style="width: 100%; height: 100%;"
                      fit="contain"
                    />
                    <div v-else style="color: #909399; font-size: 12px; text-align: center; padding: 0 10px;">
                      {{ $t('admin.products.drag_upload') }}<br>
                      <span v-if="lang.code !== 'zh-TW'" style="color: #e6a23c;">(留空將回退顯示中文圖片)</span>
                    </div>
                  </div>
                </el-col>
                <el-col :span="12">
                  <div style="margin-bottom: 10px; font-weight: bold;">{{ $t('admin.banners.mobile_image') }}</div>
                  <input type="file" accept="image/jpeg,image/png,image/webp" @change="(e) => onFileSelect(e, 'mobile', lang.code)" />
                  <div style="margin-top: 6px; font-size: 12px; color: #909399; line-height: 1.6;">
                    {{ $t('admin.banners.mobile_image_hint') }}<br>
                    {{ $t('admin.banners.image_format_hint') }}
                  </div>
                  <div style="margin-top: 8px; border: 1px dashed #dcdfe6; height: 120px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                    <el-image
                      v-if="getImageUrl('mobile', lang.code)"
                      :src="getImageUrl('mobile', lang.code)"
                      style="width: 100%; height: 100%;"
                      fit="contain"
                    />
                    <div v-else style="color: #909399; font-size: 12px; text-align: center; padding: 0 10px;">
                      {{ $t('admin.products.drag_upload') }}<br>
                      <span v-if="lang.code !== 'zh-TW'" style="color: #e6a23c;">(留空將回退顯示中文圖片)</span>
                    </div>
                  </div>
                </el-col>
              </el-row>
            </div>
          </el-tab-pane>
        </el-tabs>

        <el-form-item :label="$t('admin.banners.sort_order')" style="margin-top: 20px;">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>
        <el-form-item :label="$t('admin.banners.status')">
          <el-switch v-model="form.is_active" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false" :disabled="isSubmitting">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveBanner" :loading="isSubmitting">{{ $t('admin.actions.save') }}</el-button>
      </template>
    </el-dialog>
  </div>
</template>