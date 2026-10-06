import { createApp } from 'vue';
import { createPinia } from 'pinia';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import zhTw from 'element-plus/es/locale/lang/zh-tw';
import * as ElementPlusIconsVue from '@element-plus/icons-vue';
import App from './App.vue';
import router from './router';

// Translation store
let translations = {};

// Load translations from API
async function loadTranslations() {
    try {
        const locale = document.documentElement.lang || 'zh-TW';
        const response = await fetch(`/api/translations/${locale}?scope=admin`);
        if (response.ok) {
            translations = await response.json();
        }
    } catch (error) {
        console.warn('Failed to load translations:', error);
    }
}

// Global $t helper function
function $t(key, params = {}) {
    let text = translations[key] || key;

    // Replace :param placeholders
    Object.entries(params).forEach(([param, value]) => {
        text = text.replace(`:${param}`, value);
    });

    return text;
}

// Initialize app with translations
async function initApp() {
    await loadTranslations();

    const app = createApp(App);

    // Register all Element Plus icons
    for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
        app.component(key, component);
    }

    // Register global $t helper
    app.config.globalProperties.$t = $t;

    // Also provide it for composition API
    app.provide('$t', $t);

    app.use(createPinia());
    app.use(router);
    app.use(ElementPlus, { locale: zhTw });

    app.mount('#app');
}

initApp();
