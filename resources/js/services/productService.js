import http from './http';

export default {
    list(params = {}) {
        return http.get('/api/products', { params });
    },
    get(id) {
        return http.get(`/api/products/${id}`);
    },
    create(data) {
        return http.post('/api/products', data);
    },
    update(id, data) {
        return http.put(`/api/products/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/products/${id}`);
    },
    uploadImages(productId, formData) {
        return http.post(`/api/products/${productId}/images`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    deleteImage(productId, imageId) {
        return http.delete(`/api/products/${productId}/images/${imageId}`);
    },
    setPrimaryImage(productId, imageId) {
        return http.put(`/api/products/${productId}/images/${imageId}/primary`);
    },
    batchAction(data) {
        return http.post('/api/products/batch-action', data);
    },
};
