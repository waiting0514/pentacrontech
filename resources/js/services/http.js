import axios from 'axios';
import { useLoadingStore } from '../admin/store/modules/loading';
import { ERROR_MESSAGES } from '../constants/errors';
import { ElMessage } from 'element-plus';

const http = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept-Language': document.documentElement.lang || 'zh-TW',
    },
});

// CSRF Token
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    http.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

// Bearer Token
const apiToken = localStorage.getItem('api_token');
if (apiToken) {
    http.defaults.headers.common['Authorization'] = `Bearer ${apiToken}`;
}

// Loading interceptors
http.interceptors.request.use(
    (config) => {
        if (config.globalLoading !== false) {
            const loadingStore = useLoadingStore();
            loadingStore.startLoading();
        }
        return config;
    },
    (error) => {
        const loadingStore = useLoadingStore();
        loadingStore.stopLoading();
        return Promise.reject(error);
    }
);

http.interceptors.response.use(
    (response) => {
        if (response.config.globalLoading !== false) {
            const loadingStore = useLoadingStore();
            loadingStore.stopLoading();
        }
        return response;
    },
    (error) => {
        const loadingStore = useLoadingStore();
        if (!error.config || error.config.globalLoading !== false) {
            loadingStore.stopLoading();
        }

        if (error.response) {
            const { status, data } = error.response;

            // 情境 A：表單驗證錯誤 (HTTP 422)
            if (status === 422) {
                ElMessage.error(ERROR_MESSAGES['VALIDATION_ERROR']);
                
                // 確保呼叫端可以取得 errors 物件
                // 這裡我們直接回傳 error，但在 data 屬性中確保 errors 存在
                // Component 端通常使用 error.response.data.errors
                return Promise.reject(error);
            }

            // 情境 B：業務邏輯/系統錯誤
            // 優先讀取 error_code
            const errorCode = data?.error_code;
            let message = '';

            if (errorCode && ERROR_MESSAGES[errorCode]) {
                // 1. 查表成功
                message = ERROR_MESSAGES[errorCode];
            } else if (data?.message) {
                // 2. 查無此代碼，使用後端回傳 message
                message = data.message;
            } else {
                // 3. Fallback 預設訊息
                message = ERROR_MESSAGES['DEFAULT'];
            }
            
            // 避免重複跳轉導致的 401 訊息干擾 (因為下面會跳轉)
            if (status !== 401) {
                ElMessage.error(message);
            }
        } else {
             // 網路錯誤或其他無 response 的錯誤
             ElMessage.error(ERROR_MESSAGES['DEFAULT']);
        }

        // Handle 401 - redirect to login
        if (error.response?.status === 401) {
            localStorage.removeItem('api_token');
            // 可以選擇是否在跳轉前顯示訊息，這裡選擇保留跳轉邏輯
            window.location.href = '/admin/login';
        }

        return Promise.reject(error);
    }
);

export default http;
