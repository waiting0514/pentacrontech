import { createRouter, createWebHistory } from 'vue-router';
import Layout from '../layout/index.vue';
import { useUserStore } from '../store/modules/user';

const routes = [
    {
        path: '/admin/login',
        name: 'Login',
        component: () => import('../views/login/index.vue'),
        meta: { title: '登入', hidden: true },
    },
    {
        path: '/admin',
        component: Layout,
        redirect: '/admin/dashboard',
        children: [
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: () => import('../views/dashboard/index.vue'),
                meta: { title: '後台總覽', icon: 'HomeFilled' },
            },
            {
                path: 'banner',
                name: 'Banner',
                component: () => import('../views/banner/index.vue'),
                meta: { title: '輪播圖管理', icon: 'Picture', permission: 'banner.manage' },
            },
            {
                path: 'product',
                name: 'Product',
                component: () => import('../views/product/index.vue'),
                meta: { title: '產品管理', icon: 'Goods', permission: 'product.view' },
            },
            {
                path: 'product/category',
                name: 'ProductCategory',
                component: () => import('../views/product/category.vue'),
                meta: { title: '產品分類', icon: 'PriceTag', permission: 'product.view' },
            },
            {
                path: 'news',
                name: 'News',
                component: () => import('../views/news/index.vue'),
                meta: { title: '最新消息', icon: 'Document', permission: 'news.view' },
            },
            {
                path: 'news/category',
                name: 'NewsCategory',
                component: () => import('../views/news/category.vue'),
                meta: { title: '最新消息分類', icon: 'Folder', permission: 'news.view' },
            },
            {
                path: 'knowledge',
                name: 'Knowledge',
                component: () => import('../views/knowledge/index.vue'),
                meta: { title: '知識庫', icon: 'Reading', permission: 'knowledge.view' },
            },
            {
                path: 'knowledge/category',
                name: 'KnowledgeCategory',
                component: () => import('../views/knowledge/category.vue'),
                meta: { title: '知識庫分類', icon: 'Folder', permission: 'knowledge.view' },
            },
            {
                path: 'ai-tools',
                name: 'AiTools',
                component: () => import('../views/ai-tools/index.vue'),
                meta: { title: 'AI 工具', icon: 'MagicStick', permission: 'ai.use' },
            },
            {
                path: 'usage',
                name: 'Usage',
                component: () => import('../views/usage/index.vue'),
                meta: { title: '用量監控', icon: 'DataAnalysis', permission: 'system.manage' },
            },
            {
                path: 'orders',
                name: 'Orders',
                component: () => import('../views/orders/index.vue'),
                meta: { title: '詢價訂單', icon: 'Document', permission: 'order.view' },
            },
            {
                path: 'orders/:id',
                name: 'OrderDetail',
                component: () => import('../views/orders/detail.vue'),
                meta: { title: '訂單詳情', hidden: true, permission: 'order.view' },
            },
            {
                path: 'users',
                name: 'Users',
                component: () => import('../views/users/index.vue'),
                meta: { title: '使用者管理', icon: 'User', permission: 'user.manage' },
            },
            {
                path: 'settings',
                name: 'Settings',
                component: () => import('../views/settings/index.vue'),
                meta: { title: '系統設定', icon: 'Setting', permission: 'system.manage' },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.onError((err, to) => {
    if (err?.message?.includes('Failed to fetch dynamically imported module') ||
        err?.message?.includes('Importing a module script failed')) {
        window.location.href = to.fullPath;
    }
});

// Navigation guard
router.beforeEach(async (to, from, next) => {
    const token = localStorage.getItem('api_token');
    document.title = `${to.meta.title || 'AI Agency'} - 後台管理`;

    if (to.path !== '/admin/login' && !token) {
        next('/admin/login');
        return;
    }

    if (to.path === '/admin/login' && token) {
        next('/admin');
        return;
    }

    // Check route permission
    const requiredPermission = to.meta.permission;
    if (requiredPermission && token) {
        const userStore = useUserStore();

        // Ensure user info is loaded
        if (!userStore.userInfo) {
            try {
                await userStore.fetchUser();
            } catch {
                next('/admin/login');
                return;
            }
        }

        const { role, permissions } = userStore.userInfo || {};
        if (role === 'super-admin' || (permissions || []).includes(requiredPermission)) {
            next();
        } else {
            next('/admin/dashboard');
        }
        return;
    }

    next();
});

export default router;
