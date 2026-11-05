<script setup>
import { ref, computed, nextTick, onMounted, getCurrentInstance } from 'vue';
import { productService, productCategoryService, aiService } from '../../../services';
import { ElMessage, ElMessageBox, genFileId } from 'element-plus';
import RichTextEditor from '../../components/RichTextEditor.vue';
import TranslatableInput from '../../components/TranslatableInput.vue';
import TranslatableRichText from '../../components/TranslatableRichText.vue';
import CameraUploader from '../../components/CameraUploader.vue';
import { usePermission } from '../../composables/usePermission';
import { useSiteStore } from '../../store/modules/site';
import { useProductStore } from '../../store/modules/product';
import { useLanguageStore } from '../../store/modules/language';

const { proxy } = getCurrentInstance();
const { can } = usePermission();
const siteStore = useSiteStore();
const productStore = useProductStore();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const products = ref([]);
const categories = ref([]);
const isLoading = ref(false);
const searchQuery = ref('');
const filterCategoryId = ref('');

const showDialog = ref(false);
const isEditing = ref(false);
const currentProduct = ref(null);
const isSubmitting = ref(false);

// AI Translation state
const showTranslateDialog = ref(false);
const isTranslating = ref(false);
const translationTargets = ref([]);
const translationSource = ref('zh-TW');

// Pending image previews (from AI tool)
const pendingImagePreviews = ref([]);
const newProductFiles = ref([]);
const newProductPreviews = ref([]);

// AI Camera Dialog state
const showAiDialog = ref(false);
const pendingAiImages = ref([]);

const openTranslateDialog = () => {
  // Pre-select empty languages
  const currentName = form.value.name;
  translationTargets.value = languages.value
    .filter(l => l.code !== translationSource.value)
    .filter(l => !currentName[l.code])
    .map(l => l.code);
  
  showTranslateDialog.value = true;
};

const handleTranslate = async () => {
  if (translationTargets.value.length === 0) return;
  isTranslating.value = true;
  
  try {
    const sourceLocale = translationSource.value;
    
    // 1. Prepare batch payload
    const batch = {};
    const fields = ['name', 'description', 'meta_title', 'meta_description', 'meta_keywords'];
    
    fields.forEach(field => {
      const text = form.value[field][sourceLocale];
      if (text) batch[field] = text;
    });

    if (Object.keys(batch).length === 0) {
      ElMessage.warning('來源語系內容為空，無法翻譯');
      return;
    }
    
    // 2. Call batch translation API
    const res = await aiService.translate({
      batch,
      target_langs: translationTargets.value
    });
    
    const translatedData = res.data.data.translations; // Structure: { 'en': { 'name': '...', ... } }
    
    // 3. Update form values using immutable updates to ensure reactivity
    fields.forEach(field => {
      const updatedFieldMap = { ...form.value[field] };
      
      translationTargets.value.forEach(targetLocale => {
        if (translatedData[targetLocale] && translatedData[targetLocale][field]) {
          updatedFieldMap[targetLocale] = translatedData[targetLocale][field];
        }
      });
      
      form.value[field] = updatedFieldMap;
    });
    
    ElMessage.success('翻譯完成');
    showTranslateDialog.value = false;
  } catch (err) {
    ElMessage.error('翻譯失敗：' + (err.response?.data?.message || err.message));
  } finally {
    isTranslating.value = false;
  }
};

const form = ref({
  category_id: null,
  name: {},
  description: {},
  specs: '',
  price: 0,
  slug: {},
  meta_title: {},
  meta_description: {},
  meta_keywords: {},
});

const productImages = ref([]);
const isUploading = ref(false);
const fileInputRef = ref(null);
const selectedRows = ref([]);

const handleSelectionChange = (val) => {
  selectedRows.value = val;
};

const handleBatchInquiry = async (val) => {
  if (!selectedRows.value.length) return;
  const isInquiry = Boolean(val);
  try {
    await productService.batchAction({
      ids: selectedRows.value.map(p => p.id),
      action: 'set_inquiry_mode',
      value: isInquiry,
    });
    ElMessage.success(proxy.$t('admin.messages.batch_update_success'));
    selectedRows.value = [];
    await fetchProducts();
  } catch (err) {
    ElMessage.error(proxy.$t('admin.messages.batch_update_failed'));
  }
};

const fetchProducts = async () => {
  isLoading.value = true;
  try {
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (filterCategoryId.value) params.category_id = filterCategoryId.value;
    const response = await productService.list(params);
    const resData = response.data.data;
    products.value = resData?.data || resData;
  } catch (err) {
    ElMessage.error(proxy.$t('admin.messages.load_failed'));
  } finally {
    isLoading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await productCategoryService.list();
    categories.value = response.data.data;
  } catch (err) {
    console.error('Fetch categories error:', err);
  }
};

// 計算屬性：將分類清單重組並扁平化為帶有樹狀縮排的清單，用於下拉選單
const formattedCategories = computed(() => {
  const list = [];
  
  const buildTree = (items) => {
    const map = {};
    const tree = [];
    items.forEach(item => {
      map[item.id] = { ...item, children: [] };
    });
    items.forEach(item => {
      const node = map[item.id];
      if (item.parent_id) {
        if (map[item.parent_id]) {
          map[item.parent_id].children.push(node);
        } else {
          tree.push(node);
        }
      } else {
        tree.push(node);
      }
    });
    const sortTree = (nodes) => {
      nodes.sort((a, b) => (a.sort_order - b.sort_order) || (a.id - b.id));
      nodes.forEach(node => {
        if (node.children) {
          sortTree(node.children);
        }
      });
    };
    sortTree(tree);
    return tree;
  };

  const tree = buildTree(categories.value);

  const flattenTree = (nodes, depth = 0) => {
    nodes.forEach(node => {
      const prefix = depth > 0 ? '　'.repeat(depth - 1) + '├─ ' : '';
      const name = typeof node.name === 'object' ? (node.name?.['zh-TW'] || node.name?.en || '') : node.name;
      list.push({
        ...node,
        displayName: prefix + name
      });
      if (node.children && node.children.length > 0) {
        flattenTree(node.children, depth + 1);
      }
    });
  };

  flattenTree(tree);
  return list;
});

const openCreate = async () => {
  isEditing.value = false;
  currentProduct.value = null;
  productImages.value = [];
  attachments.value = [];
  pendingImagePreviews.value = [];
  newProductFiles.value = [];
  newProductPreviews.value.forEach(url => URL.revokeObjectURL(url));
  newProductPreviews.value = [];

  const defaultInquiry = Boolean(siteStore.settings?.product_default_inquiry_mode);

  // Initialize objects with language keys to ensure reactivity
  const langKeys = {};
  languages.value.forEach(l => langKeys[l.code] = '');

  form.value = {
    category_id: null,
    name: { ...langKeys },
    description: { ...langKeys },
    specs: '',
    price: 0,
    slug: { ...langKeys },
    meta_title: { ...langKeys },
    meta_description: { ...langKeys },
    meta_keywords: { ...langKeys },
    is_inquiry_only: defaultInquiry,
  };

  // If there are pending images from AI tool, create previews
  if (productStore.pendingImages && productStore.pendingImages.length > 0) {
    handleNewFiles(productStore.pendingImages);
  }

  showDialog.value = true;
};

const openEdit = (product) => {
  isEditing.value = true;
  currentProduct.value = product;
  productImages.value = product.images || [];
  newProductFiles.value = [];
  newProductPreviews.value.forEach(url => URL.revokeObjectURL(url));
  newProductPreviews.value = [];
  form.value = {
    category_id: product.category_id || null,
    name: { ...product.name },
    description: { ...product.description },
    specs: product.specs || '',
    price: parseFloat(product.price) || 0,
    slug: { ...product.slug },
    meta_title: { ...product.meta_title },
    meta_description: { ...product.meta_description },
    meta_keywords: { ...product.meta_keywords },
    is_inquiry_only: Boolean(product.is_inquiry_only),
  };
  showDialog.value = true;
  attachments.value = [];
  fetchAttachments();
};

const saveProduct = async () => {
  isSubmitting.value = true;
  try {
    let productId;
    if (isEditing.value) {
      await productService.update(currentProduct.value.id, form.value);
      productId = currentProduct.value.id;
    } else {
      // Build FormData for creation
      const formData = new FormData();
      if (form.value.category_id !== null) {
        formData.append('category_id', form.value.category_id);
      }
      formData.append('specs', form.value.specs || '');
      formData.append('price', form.value.price || 0);
      formData.append('is_inquiry_only', form.value.is_inquiry_only ? 1 : 0);

      // Multilingual fields
      const langFields = ['name', 'description', 'slug', 'meta_title', 'meta_description', 'meta_keywords'];
      langFields.forEach(field => {
        const values = form.value[field] || {};
        Object.keys(values).forEach(locale => {
          if (values[locale] !== undefined && values[locale] !== null) {
            formData.append(`${field}[${locale}]`, values[locale]);
          }
        });
      });

      // Append manually added files or pending AI files
      const imagesToUpload = newProductFiles.value.length > 0
        ? newProductFiles.value
        : (pendingAiImages.value.length > 0 ? pendingAiImages.value : []);
      
      imagesToUpload.forEach(file => {
        formData.append('images[]', file);
      });

      const res = await productService.create(formData);
      currentProduct.value = res.data.data;
      productId = currentProduct.value.id;
      isEditing.value = true;
    }

    productStore.clearPendingParsedData();
    pendingAiImages.value = [];
    newProductFiles.value = [];
    newProductPreviews.value.forEach(url => URL.revokeObjectURL(url));
    newProductPreviews.value = [];
    pendingImagePreviews.value.forEach(url => URL.revokeObjectURL(url));
    pendingImagePreviews.value = [];
    
    showDialog.value = false;
    ElMessage.success(proxy.$t('admin.messages.save_success'));
    await fetchProducts();
  } catch (err) {
    ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.save_failed'));
  } finally {
    isSubmitting.value = false;
  }
};

const deleteProduct = async (id) => {
  try {
    await ElMessageBox.confirm(proxy.$t('admin.messages.product_delete_confirm'), proxy.$t('admin.messages.warning'), { type: 'warning' });
    await productService.delete(id);
    ElMessage.success(proxy.$t('admin.messages.delete_success'));
    await fetchProducts();
  } catch (err) {
    if (err !== 'cancel') ElMessage.error(proxy.$t('admin.messages.delete_failed'));
  }
};

const getPrimaryThumb = (product) => {
  const primary = (product.images || []).find(i => i.is_primary);
  if (primary) return primary.paths.thumb;
  if (product.images?.length) return product.images[0].paths.thumb;
  return null;
};

// Image management
const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB

const validateImageSize = (files) => {
  const oversized = Array.from(files).filter(f => f.size > MAX_IMAGE_SIZE);
  if (oversized.length > 0) {
    ElMessage.warning(proxy.$t('admin.products.image_oversize'));
    return false;
  }
  return true;
};

const handleNewFiles = (files) => {
  if (!validateImageSize(files)) return;
  for (const file of files) {
    newProductFiles.value.push(file);
    newProductPreviews.value.push(URL.createObjectURL(file));
  }
};

const deleteNewProductPreview = (idx) => {
  URL.revokeObjectURL(newProductPreviews.value[idx]);
  newProductPreviews.value.splice(idx, 1);
  newProductFiles.value.splice(idx, 1);
};

const onFileSelect = async (event) => {
  const files = event.target.files;
  if (!files.length) return;
  if (currentProduct.value) {
    await uploadImages(files);
  } else {
    handleNewFiles(files);
  }
  event.target.value = '';
};

const onDrop = async (event) => {
  event.preventDefault();
  const files = event.dataTransfer.files;
  if (!files.length) return;
  if (currentProduct.value) {
    await uploadImages(files);
  } else {
    handleNewFiles(files);
  }
};

const uploadImages = async (files) => {
  if (!validateImageSize(files)) return;
  isUploading.value = true;
  try {
    const formData = new FormData();
    for (const file of files) formData.append('images[]', file);
    const res = await productService.uploadImages(currentProduct.value.id, formData);
    productImages.value.push(...res.data.data);
    ElMessage.success(proxy.$t('admin.messages.image_upload_success'));
  } catch (err) {
    ElMessage.error(proxy.$t('admin.messages.image_upload_failed') + '：' + (err.response?.data?.message || err.message));
  } finally {
    isUploading.value = false;
  }
};

const deleteImage = async (image) => {
  try {
    await ElMessageBox.confirm(proxy.$t('admin.messages.image_delete_confirm'), proxy.$t('admin.messages.warning'), { type: 'warning' });
    await productService.deleteImage(currentProduct.value.id, image.id);
    productImages.value = productImages.value.filter(i => i.id !== image.id);
    if (image.is_primary && productImages.value.length > 0) {
      productImages.value[0].is_primary = true;
    }
  } catch (err) {
    if (err !== 'cancel') ElMessage.error(proxy.$t('admin.messages.delete_failed'));
  }
};

const setPrimary = async (image) => {
  if (image.is_primary) return;
  try {
    await productService.setPrimaryImage(currentProduct.value.id, image.id);
    productImages.value.forEach(i => i.is_primary = (i.id === image.id));
    ElMessage.success(proxy.$t('admin.messages.primary_set_success'));
  } catch (err) {
    ElMessage.error(proxy.$t('admin.messages.primary_set_failed'));
  }
};

// ===== PDF 附件管理 =====
const ATTACHMENT_MAX_SIZE = 10 * 1024 * 1024; // 10MB
const ATTACHMENT_MAX_COUNT = 10;

const attachments = ref([]);
const isAttachmentsLoading = ref(false);
const showAttachmentDialog = ref(false);
const editingAttachment = ref(null); // null = 新增
const attachmentTitles = ref({});
const attachmentFiles = ref({}); // locale -> File（尚未上傳）
const attachmentErrors = ref({});
const attachmentActiveTab = ref('zh-TW');
const attachmentUploadRefs = {};
const isSavingAttachment = ref(false);
const removingLocale = ref('');

const defaultLocale = computed(() => {
  const d = languages.value.find(l => l.is_default) || languages.value[0];
  return d ? d.code : 'zh-TW';
});

const setUploadRef = (locale, el) => {
  if (el) attachmentUploadRefs[locale] = el;
  else delete attachmentUploadRefs[locale];
};

const formatFileSize = (bytes) => {
  if (bytes >= 1024 * 1024) return (bytes / 1024 / 1024).toFixed(1) + ' MB';
  return Math.max(1, Math.round(bytes / 1024)) + ' KB';
};

const attachmentTitleText = (att, locale) => att.title?.[locale] || '';
const attachmentFile = (att, locale) => (att.files || []).find(f => f.locale === locale);

const fetchAttachments = async () => {
  if (!currentProduct.value?.id) return;
  isAttachmentsLoading.value = true;
  try {
    const res = await productService.listAttachments(currentProduct.value.id);
    attachments.value = res.data.data || [];
  } catch (err) {
    ElMessage.error('載入附件失敗：' + (err.response?.data?.message || err.message));
  } finally {
    isAttachmentsLoading.value = false;
  }
};

const resetAttachmentForm = (att = null) => {
  editingAttachment.value = att;
  attachmentFiles.value = {};
  attachmentErrors.value = {};
  const titles = {};
  languages.value.forEach(l => { titles[l.code] = att?.title?.[l.code] || ''; });
  attachmentTitles.value = titles;
  attachmentActiveTab.value = defaultLocale.value;
};

const openAttachmentDialog = (att = null) => {
  if (!att && attachments.value.length >= ATTACHMENT_MAX_COUNT) {
    ElMessage.warning(`每個產品最多 ${ATTACHMENT_MAX_COUNT} 份附件`);
    return;
  }
  resetAttachmentForm(att);
  showAttachmentDialog.value = true;
};

const validatePdfFile = (file) => {
  if (!/\.pdf$/i.test(file.name)) {
    ElMessage.warning('僅限 PDF 檔案（副檔名需為 .pdf）');
    return false;
  }
  if (file.size > ATTACHMENT_MAX_SIZE) {
    ElMessage.warning('檔案超過 10 MB 上限');
    return false;
  }
  return true;
};

const onAttachmentFileChange = (locale, uploadFile) => {
  if (uploadFile.status !== 'ready') return;
  if (!validatePdfFile(uploadFile.raw)) {
    attachmentUploadRefs[locale]?.handleRemove(uploadFile);
    return;
  }
  attachmentFiles.value = { ...attachmentFiles.value, [locale]: uploadFile.raw };
  clearFileError(locale);
};

const onAttachmentFileRemove = (locale) => {
  const next = { ...attachmentFiles.value };
  delete next[locale];
  attachmentFiles.value = next;
  clearFileError(locale);
};

// 重新選檔或移除檔案後，清除該語系上一次伺服器回傳的檔案錯誤
const clearFileError = (locale) => {
  const key = `files.${locale}`;
  if (!(key in attachmentErrors.value)) return;
  const next = { ...attachmentErrors.value };
  delete next[key];
  attachmentErrors.value = next;
};

// 已選一個檔案後再選：以新檔取代（limit 1）
const onAttachmentFileExceed = (locale, files) => {
  const up = attachmentUploadRefs[locale];
  if (!up || !files.length) return;
  up.clearFiles();
  const file = files[0];
  file.uid = genFileId();
  up.handleStart(file);
};

const errorsFor = (locale, field) => {
  const errs = attachmentErrors.value;
  const out = [];
  Object.keys(errs).forEach(k => {
    if (k === `${field}.${locale}`) out.push(...errs[k]);
  });
  return out;
};

const generalAttachmentErrors = computed(() => {
  const out = [];
  Object.keys(attachmentErrors.value).forEach(k => {
    if (!/^(title|files)\.[^.]+$/.test(k)) out.push(...attachmentErrors.value[k]);
  });
  return out;
});

const tabHasError = (locale) => errorsFor(locale, 'title').length > 0 || errorsFor(locale, 'files').length > 0;

const saveAttachment = async () => {
  attachmentErrors.value = {};
  const isEdit = Boolean(editingAttachment.value);
  const defLoc = defaultLocale.value;
  const fileLocales = Object.keys(attachmentFiles.value);

  if (!isEdit && fileLocales.length === 0) {
    ElMessage.warning('請至少選擇一個語系的 PDF 檔案');
    return;
  }
  const defTitle = (attachmentTitles.value[defLoc] || '').trim();
  if (!defTitle) {
    attachmentActiveTab.value = defLoc;
    attachmentErrors.value = { [`title.${defLoc}`]: ['預設語系的標題為必填'] };
    ElMessage.warning('請填寫預設語系的標題');
    return;
  }

  const titles = {};
  Object.entries(attachmentTitles.value).forEach(([loc, v]) => { titles[loc] = (v || '').trim(); });

  isSavingAttachment.value = true;
  try {
    const productId = currentProduct.value.id;
    const payload = { titles, files: attachmentFiles.value };
    if (isEdit) {
      await productService.updateAttachment(productId, editingAttachment.value.id, payload);
    } else {
      await productService.createAttachment(productId, payload);
    }
    ElMessage.success('附件已儲存');
    showAttachmentDialog.value = false;
    await fetchAttachments();
  } catch (err) {
    const data = err.response?.data;
    if (err.response?.status === 422) {
      attachmentErrors.value = data?.errors || {};
      const firstLocaleKey = Object.keys(attachmentErrors.value).find(k => /^(title|files)\.[^.]+$/.test(k));
      if (firstLocaleKey) attachmentActiveTab.value = firstLocaleKey.split('.').slice(1).join('.');
      const msgs = Object.values(attachmentErrors.value).flat();
      ElMessage.error(msgs.length ? msgs.join('；') : (data?.message || '儲存附件失敗'));
    } else {
      ElMessage.error(data?.message || '儲存附件失敗：' + err.message);
    }
  } finally {
    isSavingAttachment.value = false;
  }
};

const deleteAttachment = async (att) => {
  try {
    await ElMessageBox.confirm('確定要刪除此附件（含所有語系檔案）嗎？', proxy.$t('admin.messages.warning'), { type: 'warning' });
    await productService.deleteAttachment(currentProduct.value.id, att.id);
    attachments.value = attachments.value.filter(a => a.id !== att.id);
    ElMessage.success('附件已刪除');
  } catch (err) {
    if (err !== 'cancel') ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.delete_failed'));
  }
};

const removeAttachmentLocaleFile = async (locale) => {
  const att = editingAttachment.value;
  if (!att) return;
  try {
    await ElMessageBox.confirm(`確定要移除 ${locale} 語系的檔案嗎？`, proxy.$t('admin.messages.warning'), { type: 'warning' });
    removingLocale.value = locale;
    await productService.deleteAttachmentFile(currentProduct.value.id, att.id, locale);
    editingAttachment.value = { ...att, files: (att.files || []).filter(f => f.locale !== locale) };
    ElMessage.success('已移除該語系檔案');
    await fetchAttachments();
  } catch (err) {
    if (err === 'cancel') return;
    const data = err.response?.data;
    const msg = data?.errors?.file?.[0] || data?.message || err.message;
    attachmentErrors.value = data?.errors || {};
    ElMessage.error(msg);
  } finally {
    removingLocale.value = '';
  }
};

const handleAiCreate = async ({ imageFiles, data }) => {
  if (!data) return;
  showAiDialog.value = false;
  pendingAiImages.value = imageFiles || [];
  await nextTick();
  await openCreate();
  fillFormWithParsedData(data);
  if (pendingAiImages.value.length > 0) {
    handleNewFiles(pendingAiImages.value);
  }
  ElMessage.success('已帶入 AI 解析內容與圖片');
};

const fillFormWithParsedData = (data) => {
  const defaultLang = languages.value.find(l => l.is_default) || languages.value[0];
  const defaultLocale = defaultLang ? defaultLang.code : 'zh-TW';

  form.value.name = { ...form.value.name, [defaultLocale]: data.name || '' };

  let descText = data.description || '';
  if (typeof data.specs === 'object' && data.specs !== null) {
    const specsText = Object.entries(data.specs).map(([k, v]) => `${k}: ${v}`).join('\n');
    if (specsText) {
      descText = descText ? `${descText}\n\n${specsText}` : specsText;
    }
  } else if (data.specs) {
    descText = descText ? `${descText}\n\n${data.specs}` : data.specs;
  }

  form.value.description = { ...form.value.description, [defaultLocale]: descText };
  if (typeof data.specs === 'object' && data.specs !== null) {
    form.value.specs = Object.entries(data.specs).map(([k, v]) => `${k}：${v}`).join('\n');
  } else {
    form.value.specs = data.specs || '';
  }
  form.value.meta_title = { ...form.value.meta_title, [defaultLocale]: data.meta_title || '' };

  let metaDesc = data.meta_description || '';
  if (!metaDesc && descText) {
    const plain = descText.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
    metaDesc = plain.length > 155 ? plain.substring(0, 152) + '...' : plain;
  }
  form.value.meta_description = { ...form.value.meta_description, [defaultLocale]: metaDesc };

  const keywords = Array.isArray(data.meta_keywords) ? data.meta_keywords.join(', ') : (data.meta_keywords || '');
  form.value.meta_keywords = { ...form.value.meta_keywords, [defaultLocale]: keywords };
};

onMounted(async () => {
  await Promise.all([languageStore.fetchLanguages(), fetchCategories(), fetchProducts()]);

  // Check for AI parsed data from another page (store fallback path)
  if (productStore.pendingParsedData) {
    await openCreate();
    fillFormWithParsedData(productStore.pendingParsedData);
    ElMessage.success('已帶入 AI 解析內容與圖片');
  }
});
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>{{ $t('admin.products.title') }}</h2>
      <div style="display: flex; gap: 8px;">
        <el-button v-if="siteStore.isAiEnabled" type="warning" plain @click="showAiDialog = true">
          <el-icon><MagicStick /></el-icon> AI 辨識建立
        </el-button>
        <el-button type="primary" @click="openCreate">
          <el-icon><Plus /></el-icon> {{ $t('admin.products.create') }}
        </el-button>
      </div>
    </div>

    <!-- Search & Filter -->
    <el-row :gutter="16" style="margin-bottom: 20px;">
      <el-col :span="4">
        <el-select v-model="filterCategoryId" :placeholder="$t('admin.products.all_categories')" clearable @change="fetchProducts">
          <el-option v-for="cat in formattedCategories" :key="cat.id" :label="cat.displayName" :value="cat.id" />
        </el-select>
      </el-col>
      <el-col :span="4">
        <el-dropdown :disabled="selectedRows.length === 0" @command="handleBatchInquiry">
          <el-button>
            {{ $t('admin.products.batch_actions') }} <el-icon class="el-icon--right">
              <ArrowDown />
            </el-icon>
          </el-button>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item :command="1">{{ $t('admin.products.set_inquiry_mode') }}</el-dropdown-item>
              <el-dropdown-item :command="0">{{ $t('admin.products.set_show_price') }}</el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </el-col>
      <el-col :span="14">
        <el-input v-model="searchQuery" :placeholder="$t('admin.products.search_placeholder')" clearable @keyup.enter="fetchProducts">
          <template #append>
            <el-button @click="fetchProducts"><el-icon>
                <Search />
              </el-icon></el-button>
          </template>
        </el-input>
      </el-col>
    </el-row>

    <!-- Table -->
    <el-table :data="products" border stripe v-loading="isLoading" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" />
      <el-table-column :label="$t('admin.products.image')" width="100">
        <template #default="{ row }">
          <el-image v-if="getPrimaryThumb(row)" :src="getPrimaryThumb(row)" style="width: 60px; height: 60px;"
            fit="cover" />
          <el-icon v-else :size="40" color="#dcdfe6">
            <Picture />
          </el-icon>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.categories.name')">
        <template #default="{ row }">
          <div>{{ row.name?.['zh-TW'] || row.name?.en || $t('admin.products.unnamed') }}</div>
          <div style="font-size: 12px; color: #909399;">{{ row.name?.en }}</div>
        </template>
      </el-table-column>
      <el-table-column label="Slug" width="180">
        <template #default="{ row }">
          <el-text type="info" size="small" style="font-family: monospace;">{{ row.slug?.['zh-TW'] || row.slug?.en || '-' }}</el-text>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.products.price')" width="120">
        <template #default="{ row }">
          <div v-if="row.is_inquiry_only">
            <el-tag type="info" size="small">{{ $t('admin.products.inquiry') }}</el-tag>
          </div>
          <span v-else style="font-weight: 600;">${{ row.price }}</span>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.table.actions')" width="160" align="center">
        <template #default="{ row }">
          <el-button type="primary" link @click="openEdit(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button v-if="can('product.delete')" type="danger" link @click="deleteProduct(row.id)">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Dialog -->
    <el-dialog v-model="showDialog" :title="isEditing ? $t('admin.products.edit') : $t('admin.products.create')" width="800px" destroy-on-close>
      <div v-if="siteStore.isAiEnabled" style="text-align: right; margin-bottom: 15px;">
        <el-button type="warning" plain :icon="MagicStick" @click="openTranslateDialog">
          ✨ AI 一鍵翻譯
        </el-button>
      </div>
      <el-form :model="form" label-width="120px">
        <el-form-item :label="$t('admin.products.category')">
          <el-select v-model="form.category_id" :placeholder="$t('admin.products.no_category')" clearable>
            <el-option v-for="cat in formattedCategories" :key="cat.id" :label="cat.displayName" :value="cat.id" />
          </el-select>
        </el-form-item>
        <el-form-item :label="$t('admin.products.name')" required>
          <TranslatableInput v-model="form.name" :languages="languages" :placeholder="$t('admin.products.name')" :maxlength="255" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.slug')">
          <TranslatableInput v-model="form.slug" :languages="languages" placeholder="industrial-robot" />
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">{{ $t('admin.products.slug_hint') }}</div>
        </el-form-item>
        <el-form-item :label="$t('admin.products.description')">
          <TranslatableRichText v-model="form.description" :languages="languages" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.seo_title')">
          <TranslatableInput v-model="form.meta_title" :languages="languages" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.seo_description')">
          <TranslatableInput v-model="form.meta_description" :languages="languages" type="textarea" :rows="2" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.seo_keywords')">
          <TranslatableInput v-model="form.meta_keywords" :languages="languages" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.specs')">
          <el-input v-model="form.specs" type="textarea" :rows="3" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.inquiry_mode')">
          <el-switch v-model="form.is_inquiry_only" :active-text="$t('admin.products.inquiry_only')" />
        </el-form-item>
        <el-form-item :label="$t('admin.products.price')" required>
          <el-input-number v-model="form.price" :min="0" :precision="2" :disabled="form.is_inquiry_only" />
        </el-form-item>

        <!-- Image Upload -->
        <el-form-item :label="$t('admin.products.images')">
          <div style="width: 100%;">
            <div
              style="border: 2px dashed #dcdfe6; border-radius: 8px; padding: 24px; text-align: center; cursor: pointer;"
              @click="fileInputRef?.click()" @dragover.prevent @drop="onDrop">
              <input ref="fileInputRef" type="file" multiple accept="image/jpeg,image/png,image/webp" style="display: none;"
                @change="onFileSelect" />
              <el-icon :size="32" color="#909399">
                <Upload />
              </el-icon>
              <p style="color: #909399; margin-top: 8px;">{{ isUploading ? $t('admin.products.uploading') : $t('admin.products.drag_upload') }}</p>
              <p style="color: #909399; font-size: 12px; margin-top: 4px;">{{ $t('admin.products.upload_hint') }}</p>
            </div>
            
            <!-- Existing Product Images (When Editing) -->
            <div v-if="currentProduct && productImages.length > 0"
              style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 16px;">
              <div v-for="img in productImages" :key="img.id" style="position: relative;">
                <el-image :src="img.paths.thumb"
                  style="width: 100%; aspect-ratio: 1; border-radius: 8px; cursor: pointer;"
                  :style="img.is_primary ? 'border: 3px solid #67c23a;' : 'border: 1px solid #dcdfe6;'" fit="cover"
                  @click="setPrimary(img)" />
                <el-tag v-if="img.is_primary" type="success" size="small"
                  style="position: absolute; top: 4px; left: 4px;">{{ $t('admin.products.primary_image') }}</el-tag>
                <el-button type="danger" :icon="Delete" circle size="small"
                  style="position: absolute; top: 4px; right: 4px;" @click.stop="deleteImage(img)" />
              </div>
            </div>
            <el-text v-if="currentProduct && productImages.length > 0" type="info" size="small" style="margin-top: 8px;">{{ $t('admin.products.click_to_set_primary') }}</el-text>

            <!-- Temporary Product Images Previews (When Creating) -->
            <div v-if="!currentProduct && newProductPreviews.length > 0"
              style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 16px;">
              <div v-for="(preview, idx) in newProductPreviews" :key="idx" style="position: relative;">
                <el-image :src="preview"
                  style="width: 100%; aspect-ratio: 1; border-radius: 8px; border: 1px solid #dcdfe6;" fit="cover" />
                <el-button type="danger" :icon="Delete" circle size="small"
                  style="position: absolute; top: 4px; right: 4px;" @click.stop="deleteNewProductPreview(idx)" />
              </div>
            </div>
          </div>
        </el-form-item>

        <!-- PDF Attachments (只在產品已存在時顯示) -->
        <el-form-item v-if="isEditing && currentProduct?.id" label="附件">
          <div style="width: 100%;" v-loading="isAttachmentsLoading">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
              <el-button type="primary" plain size="small" :icon="Plus" :disabled="attachments.length >= ATTACHMENT_MAX_COUNT"
                @click="openAttachmentDialog()">新增附件</el-button>
              <span style="font-size: 12px; color: #909399;">僅限 PDF，單檔上限 10 MB，每個產品最多 10 份（{{ attachments.length }}/{{ ATTACHMENT_MAX_COUNT }}）</span>
            </div>
            <div v-if="attachments.length === 0 && !isAttachmentsLoading" style="color: #909399; font-size: 13px;">尚無附件</div>
            <div v-for="att in attachments" :key="att.id"
              style="border: 1px solid #dcdfe6; border-radius: 6px; padding: 10px 12px; margin-bottom: 8px; display: flex; gap: 12px; align-items: flex-start;">
              <div style="flex: 1; min-width: 0;">
                <div v-for="l in languages" :key="l.code" v-show="attachmentTitleText(att, l.code) || attachmentFile(att, l.code)"
                  style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; line-height: 28px;">
                  <el-tag size="small" type="info">{{ l.code }}</el-tag>
                  <span style="font-weight: 600;">{{ attachmentTitleText(att, l.code) || '（未填標題）' }}</span>
                  <a v-if="attachmentFile(att, l.code)" :href="attachmentFile(att, l.code).url" target="_blank" rel="noopener"
                    style="text-decoration: none;">
                    <el-tag size="small" type="success" effect="plain">
                      {{ attachmentFile(att, l.code).original_name }}（{{ attachmentFile(att, l.code).human_size || formatFileSize(attachmentFile(att, l.code).size) }}）
                    </el-tag>
                  </a>
                </div>
              </div>
              <div style="white-space: nowrap;">
                <el-button type="primary" link @click="openAttachmentDialog(att)">{{ $t('admin.actions.edit') }}</el-button>
                <el-button type="danger" link @click="deleteAttachment(att)">{{ $t('admin.actions.delete') }}</el-button>
              </div>
            </div>
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveProduct" :loading="isSubmitting">{{ $t('admin.actions.save') }}</el-button>
      </template>
    </el-dialog>

    <!-- Attachment Dialog -->
    <el-dialog v-model="showAttachmentDialog" :title="editingAttachment ? '編輯附件' : '新增附件'" width="560px" append-to-body destroy-on-close>
      <el-alert type="info" :closable="false" show-icon style="margin-bottom: 12px;"
        title="僅限 PDF，單檔上限 10 MB，每個產品最多 10 份" />
      <el-alert v-if="generalAttachmentErrors.length" type="error" :closable="false" show-icon style="margin-bottom: 12px;"
        :title="generalAttachmentErrors.join('；')" />
      <el-tabs v-model="attachmentActiveTab">
        <el-tab-pane v-for="l in languages" :key="l.code" :name="l.code">
          <template #label>
            <span :style="tabHasError(l.code) ? 'color: #f56c6c;' : ''">{{ l.name }}<span v-if="l.code === defaultLocale"> *</span></span>
          </template>
          <el-form label-width="80px" @submit.prevent>
            <el-form-item label="標題" :required="l.code === defaultLocale" :error="errorsFor(l.code, 'title').join('；')">
              <el-input v-model="attachmentTitles[l.code]" maxlength="255" :placeholder="l.code === defaultLocale ? '必填' : '選填'" />
            </el-form-item>
            <el-form-item label="PDF 檔案" :error="errorsFor(l.code, 'files').join('；')">
              <div style="width: 100%;">
                <div v-if="editingAttachment && attachmentFile(editingAttachment, l.code)"
                  style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                  <a :href="attachmentFile(editingAttachment, l.code).url" target="_blank" rel="noopener">
                    {{ attachmentFile(editingAttachment, l.code).original_name }}
                  </a>
                  <span style="color: #909399; font-size: 12px;">
                    {{ attachmentFile(editingAttachment, l.code).human_size || formatFileSize(attachmentFile(editingAttachment, l.code).size) }}
                  </span>
                  <el-tooltip :disabled="editingAttachment.files.length > 1" content="附件至少需保留一個檔案" placement="top">
                    <span>
                      <el-button type="danger" link size="small" :disabled="editingAttachment.files.length <= 1"
                        :loading="removingLocale === l.code" @click="removeAttachmentLocaleFile(l.code)">移除此語系檔案</el-button>
                    </span>
                  </el-tooltip>
                </div>
                <el-upload :ref="(el) => setUploadRef(l.code, el)" :auto-upload="false" :limit="1" accept=".pdf,application/pdf"
                  :on-change="(f) => onAttachmentFileChange(l.code, f)" :on-remove="() => onAttachmentFileRemove(l.code)"
                  :on-exceed="(files) => onAttachmentFileExceed(l.code, files)">
                  <el-button size="small">{{ editingAttachment && attachmentFile(editingAttachment, l.code) ? '選擇 PDF（取代現有檔案）' : '選擇 PDF' }}</el-button>
                </el-upload>
              </div>
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>
      <template #footer>
        <el-button @click="showAttachmentDialog = false">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" :loading="isSavingAttachment" @click="saveAttachment">{{ $t('admin.actions.save') }}</el-button>
      </template>
    </el-dialog>

    <!-- AI Camera Dialog -->
    <el-dialog v-model="showAiDialog" title="AI 辨識建立產品" width="700px" destroy-on-close>
      <CameraUploader @create="handleAiCreate" />
    </el-dialog>

    <!-- AI Translation Dialog -->
    <el-dialog v-model="showTranslateDialog" title="AI 一鍵翻譯設定" width="400px" append-to-body>
      <div style="margin-bottom: 20px;">
        <p style="margin-bottom: 10px; font-weight: bold;">來源語系：</p>
        <el-select v-model="translationSource" style="width: 100%;">
          <el-option v-for="l in languages" :key="l.code" :label="l.name" :value="l.code" />
        </el-select>
      </div>
      <div style="margin-bottom: 20px;">
        <p style="margin-bottom: 10px; font-weight: bold;">目標語系：</p>
        <el-checkbox-group v-model="translationTargets">
          <el-checkbox v-for="l in languages.filter(lang => lang.code !== translationSource)" 
            :key="l.code" :value="l.code">
            {{ l.name }}
            <span v-if="form.name[l.code]" style="color: #f56c6c; font-size: 12px;">(已有內容)</span>
          </el-checkbox>
        </el-checkbox-group>
      </div>
      <p style="font-size: 12px; color: #909399; margin-bottom: 20px;">
        * AI 將翻譯名稱、描述、SEO 標題與關鍵字。<br>
        * 已有內容的語系若勾選，將會被 AI 生成內容覆蓋。
      </p>
      <template #footer>
        <el-button @click="showTranslateDialog = false">取消</el-button>
        <el-button type="warning" @click="handleTranslate" :loading="isTranslating" :disabled="translationTargets.length === 0">
          開始翻譯
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { Delete, ArrowDown, MagicStick, Plus } from '@element-plus/icons-vue';
export default { setup() { return { Delete, ArrowDown, MagicStick, Plus }; } };
</script>
