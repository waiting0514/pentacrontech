import { defineStore } from 'pinia';
import { authService } from '../../../services';

export const useUserStore = defineStore('user', {
    state: () => ({
        token: localStorage.getItem('api_token') || '',
        userInfo: null,
    }),
    getters: {
        isLoggedIn: (state) => !!state.token,
    },
    actions: {
        async login(credentials) {
            const response = await authService.login(credentials);
            const tokenData = response.data.data;
            this.token = tokenData.token;
            localStorage.setItem('api_token', tokenData.token);
            // Update axios default header
            const { default: http } = await import('../../../services/http');
            http.defaults.headers.common['Authorization'] = `Bearer ${tokenData.token}`;
        },
        async fetchUser() {
            const response = await authService.user();
            const data = response.data.data || response.data;
            this.userInfo = data;
        },
        async logout() {
            try {
                await authService.logout();
            } catch (e) {
                console.error('Logout error:', e);
            } finally {
                this.token = '';
                this.userInfo = null;
                localStorage.removeItem('api_token');
            }
        },
    },
});
