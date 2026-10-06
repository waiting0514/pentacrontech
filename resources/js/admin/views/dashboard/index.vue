<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import { usageService, bannerService, productService, newsService } from '../../../services';
import { usePermission } from '../../composables/usePermission';
import { useProjectStore } from '../../store/modules/project';
import { useUserStore } from '../../store/modules/user';

const { proxy } = getCurrentInstance();
const userStore = useUserStore();
const { is } = usePermission();
const projectStore = useProjectStore();

const router = useRouter();
const isLoading = ref(true);

const stats = ref({
    banners: 0,
    products: 0,
    news: 0,
    yearlyUsage: 0,
    yearlyBudget: 0,
    usagePercentage: 0,
});

const getTotal = (apiData) => {
    if (Array.isArray(apiData)) return apiData.length;
    if (typeof apiData === 'object' && apiData !== null) {
        if (apiData.meta?.total !== undefined) return apiData.meta.total;
        if (apiData.total !== undefined) return apiData.total;
        if (Array.isArray(apiData.data)) return apiData.data.length;
    }
    return 0;
};

const loadStats = async () => {
    isLoading.value = true;
    try {
        // 重新整理時 dashboard 路由沒有 permission，守衛不會先載入使用者，這裡補上
        if (!userStore.userInfo) {
            await userStore.fetchUser();
        }

        const promises = [
            bannerService.list(),
            productService.list(),
            newsService.list(),
        ];
        if (is('super-admin')) {
            promises.push(usageService.current());
        }
        const results = await Promise.allSettled(promises);

        if (results[0].status === 'fulfilled') {
            const bannerData = results[0].value.data.data;
            stats.value.banners = Array.isArray(bannerData) ? bannerData.length : (bannerData?.total || 0);
        }
        if (results[1].status === 'fulfilled') {
            stats.value.products = getTotal(results[1].value.data.data);
        }
        if (results[2].status === 'fulfilled') {
            stats.value.news = getTotal(results[2].value.data.data);
        }
        if (results[3]?.status === 'fulfilled') {
            const usage = results[3].value.data.data;
            stats.value.yearlyUsage = usage.yearly_usage_twd || 0;
            stats.value.yearlyBudget = usage.yearly_budget_twd || 0;
            stats.value.usagePercentage = usage.usage_percentage || 0;
        }
    } catch (e) {
        console.error('Load stats error:', e);
    } finally {
        isLoading.value = false;
    }
};

const navigateTo = (path) => router.push(path);

onMounted(loadStats);
</script>

<template>
  <div v-loading="isLoading">
    <h2 style="margin-bottom: 24px;">{{ $t('admin.dashboard.title') }}</h2>

    <!-- Stats Cards -->
    <el-row :gutter="20" style="margin-bottom: 24px;">
      <el-col :span="6">
        <el-card shadow="hover" class="stat-card" @click="navigateTo('/admin/banner')">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="background: linear-gradient(135deg, #409eff, #79bbff);">
              <el-icon :size="28"><Picture /></el-icon>
            </div>
            <div>
              <div class="stat-value">{{ stats.banners }}</div>
              <div class="stat-label">{{ $t('admin.dashboard.banners') }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="stat-card" @click="navigateTo('/admin/product')">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="background: linear-gradient(135deg, #67c23a, #95d475);">
              <el-icon :size="28"><Goods /></el-icon>
            </div>
            <div>
              <div class="stat-value">{{ stats.products }}</div>
              <div class="stat-label">{{ $t('admin.dashboard.products') }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="stat-card" @click="navigateTo('/admin/news')">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="background: linear-gradient(135deg, #e6a23c, #eebe77);">
              <el-icon :size="28"><Document /></el-icon>
            </div>
            <div>
              <div class="stat-value">{{ stats.news }}</div>
              <div class="stat-label">{{ $t('admin.dashboard.news') }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col v-if="is('super-admin') && projectStore.isAiEnabled" :span="6">
        <el-card shadow="hover" class="stat-card" @click="navigateTo('/admin/usage')">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
              <el-icon :size="28"><DataAnalysis /></el-icon>
            </div>
            <div>
              <div class="stat-value">NT${{ stats.yearlyUsage.toFixed(0) }}</div>
              <div class="stat-label">{{ $t('admin.dashboard.yearly_usage') }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Usage Overview -->
    <el-row :gutter="20" style="margin-bottom: 24px;">
      <el-col v-if="is('super-admin') && projectStore.isAiEnabled" :span="12">
        <el-card :header="$t('admin.dashboard.ai_usage_overview')">
          <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
            <span>{{ $t('admin.dashboard.used_this_year') }}：NT${{ stats.yearlyUsage.toFixed(0) }}</span>
            <span>{{ $t('admin.dashboard.budget') }}：NT${{ stats.yearlyBudget.toFixed(0) }}</span>
          </div>
          <el-progress
            :percentage="Math.min(stats.usagePercentage, 100)"
            :color="stats.usagePercentage >= 90 ? '#f56c6c' : stats.usagePercentage >= 70 ? '#e6a23c' : '#67c23a'"
            :stroke-width="20"
          />
        </el-card>
      </el-col>
      <el-col :span="projectStore.isAiEnabled ? 12 : 24">
        <el-card :header="$t('admin.dashboard.quick_actions')">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <el-button type="primary" @click="navigateTo('/admin/product')">
              <el-icon><Goods /></el-icon> {{ $t('admin.dashboard.manage_products') }}
            </el-button>
            <el-button type="success" @click="navigateTo('/admin/news')">
              <el-icon><Document /></el-icon> {{ $t('admin.dashboard.manage_news') }}
            </el-button>
            <el-button type="warning" @click="navigateTo('/admin/banner')">
              <el-icon><Picture /></el-icon> {{ $t('admin.dashboard.manage_banners') }}
            </el-button>
            <!-- AI Tools temporarily hidden -->
            <!--
            <el-button v-if="projectStore.isAiEnabled" @click="navigateTo('/admin/ai-tools')">
              <el-icon><MagicStick /></el-icon> {{ $t('admin.dashboard.ai_tools') }}
            </el-button>
            -->
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<style scoped>
.stat-card {
    cursor: pointer;
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-4px);
}
.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #303133;
}
.stat-label {
    font-size: 13px;
    color: #909399;
    margin-top: 2px;
}
</style>
