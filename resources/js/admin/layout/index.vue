<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import Sidebar from './components/Sidebar.vue';
import Navbar from './components/Navbar.vue';
import { useProjectStore } from '../store/modules/project';

const isCollapse = ref(false);
const projectStore = useProjectStore();
const router = useRouter();

const isNavigating = ref(false);
const navDone = ref(false);
let navTimer = null;
let removeBeforeEach = null;
let removeAfterEach = null;

const toggleSidebar = () => {
    isCollapse.value = !isCollapse.value;
};

onMounted(() => {
    projectStore.fetchCurrentProject();

    removeBeforeEach = router.beforeEach(() => {
        if (navTimer) clearTimeout(navTimer);
        navDone.value = false;
        isNavigating.value = true;
    });

    removeAfterEach = router.afterEach(() => {
        isNavigating.value = false;
        navDone.value = true;
        navTimer = setTimeout(() => {
            navDone.value = false;
        }, 400);
    });
});

onUnmounted(() => {
    removeBeforeEach?.();
    removeAfterEach?.();
    if (navTimer) clearTimeout(navTimer);
});
</script>

<template>
  <div class="app-wrapper">
    <!-- 全螢幕 loading 遮罩，阻擋所有點擊 -->
    <transition name="nav-overlay">
      <div v-if="isNavigating" class="nav-overlay">
        <div class="nav-spinner"></div>
      </div>
    </transition>
    <!-- 頂部完成動畫條（僅在完成後短暫顯示） -->
    <div class="route-progress" :class="{ 'is-done': navDone }"></div>

    <Sidebar :is-collapse="isCollapse" />
    <div class="main-container" :class="{ 'is-collapse': isCollapse }">
      <Navbar :is-collapse="isCollapse" @toggle-sidebar="toggleSidebar" />
      <div class="app-main">
        <router-view v-slot="{ Component }">
          <transition name="fade-transform" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-wrapper {
    display: flex;
    height: 100vh;
    width: 100%;
}

.main-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 220px;
    transition: margin-left 0.3s;
    overflow: hidden;
}

.main-container.is-collapse {
    margin-left: 64px;
}

.app-main {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #f0f2f5;
}

.fade-transform-enter-active,
.fade-transform-leave-active {
    transition: all 0.3s;
}

.fade-transform-enter-from {
    opacity: 0;
    transform: translateX(-10px);
}

.fade-transform-leave-to {
    opacity: 0;
    transform: translateX(10px);
}

/* 全螢幕遮罩 */
.nav-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: not-allowed;
}

.nav-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e4e7ed;
    border-top-color: #409eff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* 遮罩進出動畫 */
.nav-overlay-enter-active {
    transition: opacity 0.15s ease;
}
.nav-overlay-leave-active {
    transition: opacity 0.25s ease;
}
.nav-overlay-enter-from,
.nav-overlay-leave-to {
    opacity: 0;
}

/* 頂部完成條 */
.route-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    width: 0;
    background: linear-gradient(to right, #409eff, #67c23a);
    z-index: 10000;
    opacity: 0;
    pointer-events: none;
}

.route-progress.is-done {
    width: 100%;
    opacity: 0;
    transition: width 0.15s ease, opacity 0.4s ease 0.1s;
}
</style>
