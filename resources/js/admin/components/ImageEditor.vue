<script setup>
import { ref } from 'vue';
import { aiService } from '../../services';
import { ElMessage, ElMessageBox } from 'element-plus';

const props = defineProps({
    imageUrl: { type: String, required: true },
});

const emit = defineEmits(['updated']);

const currentImage = ref(props.imageUrl);
const isProcessing = ref(false);
const scenePrompt = ref('');
const showPromptInput = ref(false);

const removeBackground = async () => {
    try {
        await ElMessageBox.confirm('確定要移除背景嗎？', '提示', { type: 'warning' });
    } catch { return; }

    isProcessing.value = true;
    try {
        const response = await fetch(props.imageUrl);
        const blob = await response.blob();
        const file = new File([blob], 'image.jpg', { type: blob.type });
        const formData = new FormData();
        formData.append('image', file);
        const result = await aiService.removeBackground(formData);
        currentImage.value = result.data.data.image_url;
        emit('updated', result.data.data.image_url);
        ElMessage.success('背景移除成功');
    } catch (err) {
        ElMessage.error(err.response?.data?.message || '背景移除失敗');
    } finally {
        isProcessing.value = false;
    }
};

const generateScene = async () => {
    if (!scenePrompt.value.trim()) { ElMessage.warning('請輸入場景描述'); return; }
    isProcessing.value = true;
    try {
        const response = await fetch(props.imageUrl);
        const blob = await response.blob();
        const file = new File([blob], 'image.jpg', { type: blob.type });
        const formData = new FormData();
        formData.append('image', file);
        formData.append('prompt', scenePrompt.value);
        const result = await aiService.generateScene(formData);
        currentImage.value = result.data.data.image_url;
        emit('updated', result.data.data.image_url);
        showPromptInput.value = false;
        scenePrompt.value = '';
        ElMessage.success('場景生成成功');
    } catch (err) {
        ElMessage.error(err.response?.data?.message || '場景生成失敗');
    } finally {
        isProcessing.value = false;
    }
};

const togglePromptInput = () => {
    showPromptInput.value = !showPromptInput.value;
    if (!showPromptInput.value) scenePrompt.value = '';
};
</script>

<template>
  <div v-loading="isProcessing" element-loading-text="處理中，請稍候...">
    <el-image :src="currentImage" fit="contain" style="width: 100%; margin-bottom: 16px;" />

    <div style="display: flex; gap: 12px; margin-bottom: 16px;">
      <el-button type="primary" @click="removeBackground" :disabled="isProcessing">
        <el-icon><Brush /></el-icon> 移除背景
      </el-button>
      <el-button type="success" @click="togglePromptInput" :disabled="isProcessing">
        <el-icon><PictureFilled /></el-icon> {{ showPromptInput ? '取消' : '生成場景' }}
      </el-button>
    </div>

    <div v-if="showPromptInput">
      <el-input v-model="scenePrompt" type="textarea" :rows="3" placeholder="例如：在現代辦公室桌上，陽光從窗戶灑進來..." style="margin-bottom: 12px;" />
      <el-button type="success" @click="generateScene" :disabled="isProcessing || !scenePrompt.trim()">生成</el-button>
    </div>

    <el-alert type="info" :closable="false" style="margin-top: 16px;">
      <template #title>使用提示</template>
      <ul style="margin: 0; padding-left: 16px; font-size: 13px;">
        <li><strong>移除背景</strong>：自動去除圖片背景，適合產品圖</li>
        <li><strong>生成場景</strong>：將產品放置在指定場景中</li>
      </ul>
    </el-alert>
  </div>
</template>
