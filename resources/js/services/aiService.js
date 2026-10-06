import http from './http';

export default {
    chat(data) {
        return http.post('/api/ai/chat', data);
    },
    translate(data) {
        return http.post('/api/ai/translate', data);
    },
    parseImage(formData) {
        return http.post('/api/ai/parse-image', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    /**
     * Parse multiple images as a single context for comprehensive product analysis.
     * Use for: DM + packaging, multiple angles, or product variants.
     * @param {FormData} formData - FormData with images[] and locale
     * @returns {Promise} API response with combined analysis result
     */
    parseImages(formData) {
        return http.post('/api/ai/parse-images', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            timeout: 120000, // 120 seconds timeout for multi-image processing
        });
    },
    removeBackground(formData) {
        return http.post('/api/ai/remove-background', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    generateScene(formData) {
        return http.post('/api/ai/generate-scene', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
};
