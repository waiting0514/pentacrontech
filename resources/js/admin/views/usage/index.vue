<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { usageService } from '../../../services';
import { ElMessage } from 'element-plus';
import { usePermission } from '../../composables/usePermission';

const { proxy } = getCurrentInstance();
const { can, is } = usePermission();

const currentUsage = ref(null);
const logs = ref([]);
const statistics = ref(null);
const isLoading = ref(false);

const usagePercentage = computed(() => currentUsage.value?.usage_percentage || 0);

const progressStatus = computed(() => {
    if (usagePercentage.value >= 90) return 'exception';
    if (usagePercentage.value >= 70) return 'warning';
    return 'success';
});

const fetchCurrentUsage = async () => {
    const response = await usageService.current();
    currentUsage.value = response.data.data;
};

const fetchLogs = async () => {
    const response = await usageService.logs();
    const resData = response.data.data;
    logs.value = resData?.data || resData;
};

const fetchStatistics = async () => {
    const response = await usageService.statistics();
    statistics.value = response.data.data;
};

const getProviderLabel = (provider) => {
    const map = {
        openai: proxy.$t('admin.usage.provider_openai'),
        photoroom: proxy.$t('admin.usage.provider_photoroom'),
        clipdrop: proxy.$t('admin.usage.provider_clipdrop'),
    };
    return map[provider] || provider;
};

const getFeatureLabel = (feature) => {
    const map = {
        chat: proxy.$t('admin.usage.feature_chat'),
        translation: proxy.$t('admin.usage.feature_translation'),
        vision_parse: proxy.$t('admin.usage.feature_vision_parse'),
        vision_batch_parse: proxy.$t('admin.usage.feature_vision_batch_parse'),
        vision_parse_multi: proxy.$t('admin.usage.feature_vision_parse_multi'),
        remove_bg: proxy.$t('admin.usage.feature_remove_bg'),
        scene_gen: proxy.$t('admin.usage.feature_scene_gen'),
    };
    return map[feature] || feature;
};

onMounted(async () => {
    isLoading.value = true;
    try {
        await Promise.all([fetchCurrentUsage(), fetchLogs(), fetchStatistics()]);
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
  <div v-loading="isLoading">
    <h2 style="margin-bottom: 20px;">{{ $t('admin.usage.title') }}</h2>

    <!-- Current Usage -->
    <el-card v-if="currentUsage" style="margin-bottom: 20px; background: linear-gradient(135deg, #409eff, #7c3aed); color: #fff;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="color: #fff; margin: 0;">{{ $t('admin.usage.yearly_usage') }}</h3>
        <el-tag v-if="currentUsage.project_name" effect="dark" round>{{ currentUsage.project_name }}</el-tag>
      </div>
      <el-row :gutter="20" style="margin-bottom: 16px;">
        <el-col :span="6">
          <p style="opacity: 0.8; font-size: 13px;">{{ $t('admin.usage.used') }}</p>
          <p style="font-size: 24px; font-weight: 700;">NT${{ currentUsage.yearly_usage_twd.toFixed(0) }}</p>
        </el-col>
        <el-col :span="6">
          <p style="opacity: 0.8; font-size: 13px;">{{ $t('admin.usage.budget') }}</p>
          <p style="font-size: 24px; font-weight: 700;">NT${{ currentUsage.yearly_budget_twd.toFixed(0) }}</p>
        </el-col>
        <el-col :span="6">
          <p style="opacity: 0.8; font-size: 13px;">{{ $t('admin.usage.remaining') }}</p>
          <p style="font-size: 24px; font-weight: 700;">NT${{ currentUsage.remaining_budget_twd.toFixed(0) }}</p>
        </el-col>
        <el-col :span="6">
          <p style="opacity: 0.8; font-size: 13px;">{{ $t('admin.usage.usage_rate') }}</p>
          <p style="font-size: 24px; font-weight: 700;">{{ usagePercentage.toFixed(1) }}%</p>
        </el-col>
      </el-row>
      <el-progress :percentage="Math.min(usagePercentage, 100)" :status="progressStatus" :stroke-width="12" :show-text="false" />
      <el-alert v-if="usagePercentage >= 90" :title="$t('admin.usage.warning_near_limit')" type="warning" :closable="false" style="margin-top: 12px;" />
    </el-card>

    <!-- Statistics -->
    <el-card v-if="statistics" :header="$t('admin.usage.statistics')" style="margin-bottom: 20px;">
      <el-table :data="statistics.statistics" border stripe>
        <el-table-column :label="$t('admin.usage.service')">
          <template #default="{ row }">{{ getProviderLabel(row.provider) }}</template>
        </el-table-column>
        <el-table-column :label="$t('admin.usage.feature')">
          <template #default="{ row }">{{ getFeatureLabel(row.feature) }}</template>
        </el-table-column>
        <el-table-column :label="$t('admin.usage.count')" prop="count" align="right" />
        <el-table-column :label="$t('admin.usage.total_cost_twd')" align="right">
          <template #default="{ row }">NT${{ parseFloat(row.total_cost_twd).toFixed(0) }}</template>
        </el-table-column>
        <el-table-column v-if="is('super-admin')" :label="$t('admin.usage.total_cost_usd')" align="right">
          <template #default="{ row }">${{ parseFloat(row.total_cost_usd).toFixed(6) }}</template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- Logs -->
    <el-card :header="$t('admin.usage.logs')">
      <el-table :data="logs" border stripe>
        <el-table-column :label="$t('admin.usage.time')" width="180">
          <template #default="{ row }">{{ new Date(row.created_at).toLocaleString('zh-TW') }}</template>
        </el-table-column>
        <el-table-column :label="$t('admin.usage.service')">
          <template #default="{ row }">{{ getProviderLabel(row.provider) }}</template>
        </el-table-column>
        <el-table-column :label="$t('admin.usage.feature')">
          <template #default="{ row }">{{ getFeatureLabel(row.feature) }}</template>
        </el-table-column>
        <el-table-column v-if="is('super-admin')" :label="$t('admin.usage.tokens')" align="right">
          <template #default="{ row }">
            <span v-if="row.input_tokens">{{ row.input_tokens }} / {{ row.output_tokens }}</span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column :label="$t('admin.usage.cost_twd')" align="right">
          <template #default="{ row }">NT${{ parseFloat(row.display_cost_twd || 0).toFixed(0) }}</template>
        </el-table-column>
        <el-table-column v-if="is('super-admin')" :label="$t('admin.usage.cost_usd')" align="right">
          <template #default="{ row }">${{ parseFloat(row.cost_usd).toFixed(6) }}</template>
        </el-table-column>
        <el-table-column :label="$t('admin.table.status')" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'success' ? 'success' : 'danger'" size="small">
              {{ row.status === 'success' ? $t('admin.usage.success') : $t('admin.usage.failed') }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
