import { defineStore } from 'pinia';

export const useProductStore = defineStore('product', {
    state: () => ({
        pendingParsedData: null,
        pendingImages: [],
    }),
    actions: {
        setPendingParsedData(data, imageFiles = []) {
            this.pendingParsedData = data;
            // Support both single file and array of files
            this.pendingImages = Array.isArray(imageFiles) ? imageFiles : (imageFiles ? [imageFiles] : []);
        },
        clearPendingParsedData() {
            this.pendingParsedData = null;
            this.pendingImages = [];
        }
    }
});
