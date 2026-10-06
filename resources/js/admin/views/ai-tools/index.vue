<script setup>
import { ref, computed, getCurrentInstance, onMounted } from 'vue';
import { aiService } from '../../../services';
import { useSiteStore } from '../../store/modules/site';

const { proxy } = getCurrentInstance();
const siteStore = useSiteStore();

const messages = ref([]);
const userMessage = ref('');
const ragQuery = ref('');
const isLoading = ref(false);
const context = ref('product');

onMounted(() => {
    if (!siteStore.isAiEnabled) {
        ElMessage.warning('AI features are disabled for this site.');
    }
});

const contextLabel = computed(() => context.value === 'product' ? proxy.$t('admin.ai.product_assistant') : proxy.$t('admin.ai.news_assistant'));

const sendMessage = async () => {
    if (!userMessage.value.trim()) return;
    const message = userMessage.value;
    userMessage.value = '';

    messages.value.push({ role: 'user', content: message, timestamp: new Date() });
    isLoading.value = true;

    try {
        const response = await aiService.chat({
            context: context.value,
            message,
            rag_query: ragQuery.value || null,
        });
        messages.value.push({
            role: 'assistant',
            content: response.data.data.response,
            timestamp: new Date(),
        });
    } catch (err) {
        ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.error_occurred'));
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
  <div>
    <h2 style="margin-bottom: 20px;">{{ $t('admin.ai.title') }}</h2>

    <el-card style="height: 600px; display: flex; flex-direction: column;">
      <template #header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <span>{{ contextLabel }}</span>
          <el-radio-group v-model="context" size="small">
            <el-radio-button value="product">{{ $t('admin.ai.product') }}</el-radio-button>
            <el-radio-button value="news">{{ $t('admin.ai.news') }}</el-radio-button>
          </el-radio-group>
        </div>
      </template>

      <!-- Messages -->
      <div style="flex: 1; overflow-y: auto; margin-bottom: 16px;">
        <div v-if="messages.length === 0" style="text-align: center; color: #909399; padding-top: 80px;">
          <el-icon :size="48"><ChatDotRound /></el-icon>
          <p style="margin-top: 16px;">{{ $t('admin.ai.start_chat') }}</p>
        </div>
        <div v-for="(msg, i) in messages" :key="i"
          :style="{ display: 'flex', justifyContent: msg.role === 'user' ? 'flex-end' : 'flex-start', marginBottom: '12px' }">
          <div :style="{
            maxWidth: '70%', padding: '10px 14px', borderRadius: '8px',
            background: msg.role === 'user' ? '#409eff' : '#f0f2f5',
            color: msg.role === 'user' ? '#fff' : '#303133',
          }">
            <p style="white-space: pre-wrap;">{{ msg.content }}</p>
            <p style="font-size: 11px; opacity: 0.7; margin-top: 4px;">
              {{ msg.timestamp.toLocaleTimeString('zh-TW', { hour: '2-digit', minute: '2-digit' }) }}
            </p>
          </div>
        </div>
        <div v-if="isLoading" style="display: flex; gap: 4px; padding: 8px;">
          <span style="width: 8px; height: 8px; background: #909399; border-radius: 50; animation: bounce 0.6s infinite;"></span>
          <span style="width: 8px; height: 8px; background: #909399; border-radius: 50; animation: bounce 0.6s infinite 0.1s;"></span>
          <span style="width: 8px; height: 8px; background: #909399; border-radius: 50; animation: bounce 0.6s infinite 0.2s;"></span>
        </div>
      </div>

      <!-- Input -->
      <div>
        <el-input v-if="context === 'product'" v-model="ragQuery" :placeholder="$t('admin.ai.search_products')" size="small" style="margin-bottom: 8px;" />
        <div style="display: flex; gap: 8px;">
          <el-input v-model="userMessage" :placeholder="$t('admin.ai.input_message')" @keyup.enter="sendMessage" :disabled="isLoading" />
          <el-button type="primary" @click="sendMessage" :disabled="isLoading || !userMessage.trim()">{{ $t('admin.ai.send') }}</el-button>
        </div>
      </div>
    </el-card>
  </div>
</template>
