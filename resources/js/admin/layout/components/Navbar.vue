<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../../store/modules/user';
import { Fold, Expand } from '@element-plus/icons-vue';

const props = defineProps({
    isCollapse: Boolean,
});

const emit = defineEmits(['toggle-sidebar']);

const router = useRouter();
const userStore = useUserStore();

const userName = computed(() => userStore.userInfo?.name || '載入中...');
const userInitial = computed(() =>
    userStore.userInfo?.name?.charAt(0).toUpperCase() || 'U'
);

onMounted(async () => {
    if (userStore.isLoggedIn && !userStore.userInfo) {
        try {
            await userStore.fetchUser();
        } catch (e) {
            console.error('Failed to fetch user:', e);
        }
    }
});

const handleLogout = async () => {
    await userStore.logout();
    router.push('/admin/login');
};
</script>

<template>
  <div class="navbar">
    <div class="left-section">
      <el-icon class="hamburger" @click="emit('toggle-sidebar')">
        <Fold v-if="!isCollapse" />
        <Expand v-else />
      </el-icon>
    </div>

    <div class="right-section">
      <el-dropdown trigger="click">
        <div class="avatar-wrapper">
          <div class="user-avatar">{{ userInitial }}</div>
          <span class="user-name">{{ userName }}</span>
          <el-icon><ArrowDown /></el-icon>
        </div>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item @click="handleLogout">
              <el-icon><SwitchButton /></el-icon>
              {{ $t('admin.nav.logout') }}
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </div>
</template>

<style scoped>
.navbar {
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
}

.hamburger {
    font-size: 20px;
    cursor: pointer;
    color: #5a5e66;
}

.hamburger:hover {
    color: #409eff;
}

.right-section {
    display: flex;
    align-items: center;
}

.avatar-wrapper {
    display: flex;
    align-items: center;
    cursor: pointer;
    gap: 8px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #409eff, #7c3aed);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.user-name {
    font-size: 14px;
    color: #5a5e66;
}
</style>
