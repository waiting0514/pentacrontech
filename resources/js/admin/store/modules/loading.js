import { defineStore } from 'pinia';

export const useLoadingStore = defineStore('loading', {
    state: () => ({
        loadingCount: 0,
    }),
    getters: {
        isLoading: (state) => state.loadingCount > 0,
    },
    actions: {
        startLoading() {
            this.loadingCount++;
        },
        stopLoading() {
            if (this.loadingCount > 0) {
                this.loadingCount--;
            }
        },
        resetLoading() {
            this.loadingCount = 0;
        },
    },
});
