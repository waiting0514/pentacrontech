<script setup>
import { ref, computed } from 'vue';
import { aiService } from '../../services';
import { ElMessage } from 'element-plus';
import { Plus, Close, Camera, Delete, RefreshRight, Loading } from '@element-plus/icons-vue';

const emit = defineEmits(['parsed', 'create']);

// State management
const imageFiles = ref([]);
const isLoading = ref(false);
const parsedData = ref(null);
const error = ref(null);
let nextImageId = 1;

// Constants
const MAX_IMAGES = 5;
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

// Computed
const hasImages = computed(() => imageFiles.value.length > 0);
const imageCount = computed(() => imageFiles.value.length);
const canAddMore = computed(() => imageFiles.value.length < MAX_IMAGES);
const canScan = computed(() => imageFiles.value.length > 0 && !isLoading.value && !parsedData.value);

// Handle file selection (supports multiple files)
const handleFileSelect = (uploadFile) => {
    const file = uploadFile.raw || uploadFile;
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
        ElMessage.warning('請選擇圖片檔案');
        return;
    }

    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
        ElMessage.warning('檔案大小不能超過 10MB');
        return;
    }

    // Check max images limit
    if (imageFiles.value.length >= MAX_IMAGES) {
        ElMessage.warning(`最多只能上傳 ${MAX_IMAGES} 張圖片`);
        return;
    }

    // Create preview URL immediately
    const previewUrl = URL.createObjectURL(file);

    // Create image object with preview ready
    const imageObj = {
        id: nextImageId++,
        file: file,
        preview: previewUrl,
    };

    imageFiles.value.push(imageObj);
};

// Remove image
const removeImage = (imageId) => {
    const index = imageFiles.value.findIndex(img => img.id === imageId);
    if (index !== -1) {
        imageFiles.value.splice(index, 1);
    }
    // Clear result if images changed
    if (parsedData.value) {
        parsedData.value = null;
    }
};

// Scan all images together
const scanImages = async () => {
    if (imageFiles.value.length === 0) return;

    isLoading.value = true;
    error.value = null;
    parsedData.value = null;

    try {
        const formData = new FormData();

        // Append all images
        imageFiles.value.forEach((img) => {
            formData.append('images[]', img.file);
        });

        formData.append('locale', document.documentElement.lang || 'zh-TW');

        const response = await aiService.parseImages(formData);
        parsedData.value = response.data.data;

        emit('parsed', { data: parsedData.value.data, usage: parsedData.value.usage });
        ElMessage.success('解析完成');

    } catch (err) {
        error.value = err.response?.data?.message || '解析失敗，請稍後再試';
        ElMessage.error(error.value);
    } finally {
        isLoading.value = false;
    }
};

// Retry scan
const retryScan = () => {
    error.value = null;
    scanImages();
};

// Create product from parsed data
const handleCreateProduct = () => {
    if (!parsedData.value?.data) return;

    // Pass all images
    const allImages = imageFiles.value.map(img => img.file);

    emit('create', { imageFiles: allImages, data: parsedData.value.data });
};

// Reset all
const reset = () => {
    imageFiles.value = [];
    isLoading.value = false;
    parsedData.value = null;
    error.value = null;
};
</script>

<template>
  <div>
    <!-- Upload Area (when no images) -->
    <el-upload
      v-if="!hasImages"
      drag
      multiple
      :auto-upload="false"
      accept="image/*"
      :show-file-list="false"
      @change="handleFileSelect"
    >
      <el-icon style="font-size: 48px; color: #909399;"><Camera /></el-icon>
      <div style="margin-top: 12px;">點擊或拖曳上傳圖片（可多選）</div>
      <template #tip>
        <div style="font-size: 12px; color: #909399;">
          支援同一產品的多張圖片（DM、包裝、不同角度），最多 {{ MAX_IMAGES }} 張
        </div>
      </template>
    </el-upload>

    <!-- Image Grid & Results (when has images) -->
    <div v-else>
      <!-- Action Bar -->
      <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
        <el-tag type="info">{{ imageCount }} 張圖片</el-tag>

        <div style="flex: 1;"></div>

        <!-- Add more button -->
        <el-upload
          v-if="canAddMore && !parsedData"
          :auto-upload="false"
          accept="image/*"
          multiple
          :show-file-list="false"
          @change="handleFileSelect"
        >
          <el-button size="small" :icon="Plus">添加更多</el-button>
        </el-upload>

        <el-button size="small" :icon="Delete" @click="reset">重設</el-button>
        <el-button
          v-if="!parsedData"
          type="primary"
          size="small"
          @click="scanImages"
          :loading="isLoading"
          :disabled="!canScan"
        >
          {{ isLoading ? 'AI 分析中...' : '開始掃描' }}
        </el-button>
      </div>

      <!-- Image Thumbnails Grid -->
      <div
        v-if="!parsedData"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-bottom: 16px;"
      >
        <div
          v-for="image in imageFiles"
          :key="image.id"
          style="position: relative; aspect-ratio: 1; background: #f5f7fa; border-radius: 8px; overflow: hidden;"
        >
          <el-image
            v-if="image.preview"
            :src="image.preview"
            fit="cover"
            style="width: 100%; height: 100%;"
          />
          <el-button
            type="danger"
            :icon="Close"
            circle
            size="small"
            style="position: absolute; top: 4px; right: 4px;"
            @click="removeImage(image.id)"
            :disabled="isLoading"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div
        v-if="isLoading"
        style="text-align: center; padding: 40px 20px; background: #f5f7fa; border-radius: 8px;"
      >
        <el-icon class="is-loading" style="font-size: 32px; color: #409eff;">
          <Loading />
        </el-icon>
        <p style="margin-top: 12px; color: #606266;">
          AI 正在綜合分析 {{ imageCount }} 張圖片...
        </p>
      </div>

      <!-- Error State -->
      <el-alert
        v-if="error && !isLoading"
        :title="error"
        type="error"
        show-icon
        style="margin-bottom: 16px;"
      >
        <template #default>
          <el-button size="small" :icon="RefreshRight" @click="retryScan" style="margin-top: 8px;">
            重試
          </el-button>
        </template>
      </el-alert>

      <!-- Parsed Result -->
      <div v-if="parsedData && parsedData.data" style="background: #f0f9eb; border: 1px solid #e1f3d8; border-radius: 8px; padding: 16px;">
        <div style="margin-bottom: 12px;">
          <el-tag type="success">解析完成</el-tag>
        </div>

        <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #303133;">
          {{ parsedData.data.name }}
        </h3>

        <div v-if="parsedData.data.specs" style="margin-bottom: 8px;">
          <strong style="color: #606266;">規格：</strong>
          <span style="color: #909399;">{{ parsedData.data.specs }}</span>
        </div>

        <div v-if="parsedData.data.description" style="margin-bottom: 8px;">
          <strong style="color: #606266;">描述：</strong>
          <span style="color: #909399; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
            {{ parsedData.data.description }}
          </span>
        </div>

        <div v-if="parsedData.data.variants && parsedData.data.variants.length > 0" style="margin-bottom: 12px;">
          <strong style="color: #606266;">變體：</strong>
          <el-tag
            v-for="(variant, idx) in parsedData.data.variants"
            :key="idx"
            size="small"
            style="margin-left: 4px;"
          >
            {{ variant.color || variant.size || JSON.stringify(variant) }}
          </el-tag>
        </div>

        <el-button type="primary" :icon="Plus" @click="handleCreateProduct">
          建立產品
        </el-button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.is-loading {
  animation: rotating 1.5s linear infinite;
}

@keyframes rotating {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
