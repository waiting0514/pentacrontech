import { defineStore } from 'pinia';
import { settingsService } from '../../../services';

export const useSiteStore = defineStore('site', {
    state: () => ({
        settings: null,
        isLoading: false,
    }),
    getters: {
        isAiEnabled: (state) => state.settings?.is_ai_enabled ?? true,
    },
    actions: {
        async fetchSettings() {
            this.isLoading = true;
            try {
                const response = await settingsService.get();
                this.settings = response.data.data;
                return this.settings;
            } catch (error) {
                console.error('Failed to fetch site settings:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
        updateSettings(data) {
            if (this.settings) {
                this.settings = { ...this.settings, ...data };
            }
        }
    }
});
