<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePermission } from '../../composables/usePermission';
import { useProjectStore } from '../../store/modules/project';

const props = defineProps({
    isCollapse: Boolean,
});

const route = useRoute();
const router = useRouter();
const { can } = usePermission();
const projectStore = useProjectStore();

const activeMenu = computed(() => route.path);

const menuItems = computed(() => {
    const layoutRoute = router.options.routes.find(r => r.path === '/admin');
    if (!layoutRoute?.children) return [];
    return layoutRoute.children.filter(item => {
        if (item.meta?.hidden) return false;

        // Temporarily hide AI Tools
        if (item.path === 'ai-tools') return false;

        // Hide Usage if AI is disabled
        if (!projectStore.isAiEnabled && item.path === 'usage') {
            return false;
        }

        if (item.meta?.permission) return can(item.meta.permission);

        return true;
    });
});
</script>

<template>
  <div class="sidebar-container" :class="{ 'is-collapse': isCollapse }">
    <div class="sidebar-logo">
      <h1 v-if="!isCollapse">AI Agency</h1>
      <h1 v-else>AI</h1>
    </div>
    <el-scrollbar>
      <el-menu
        :default-active="activeMenu"
        :collapse="isCollapse"
        :collapse-transition="false"
        router
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409eff"
      >
        <el-menu-item
          v-for="item in menuItems"
          :key="item.path"
          :index="'/admin/' + item.path"
        >
          <el-icon>
            <component :is="item.meta?.icon" />
          </el-icon>
          <template #title>{{ item.meta?.title }}</template>
        </el-menu-item>
      </el-menu>
    </el-scrollbar>
  </div>
</template>

<style scoped>
.sidebar-container {
    width: 220px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1001;
    background-color: #304156;
    transition: width 0.3s;
    overflow: hidden;
}

.sidebar-container.is-collapse {
    width: 64px;
}

.sidebar-logo {
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #263445;
}

.sidebar-logo h1 {
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
}

:deep(.el-menu) {
    border-right: none;
}
</style>
