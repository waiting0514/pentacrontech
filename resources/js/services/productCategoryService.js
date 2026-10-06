import http from './http';

export default {
    list() {
        return http.get('/api/product-categories');
    },
    create(data) {
        return http.post('/api/product-categories', data);
    },
    update(id, data) {
        if (data instanceof FormData) {
            data.append('_method', 'PUT');
            return http.post(`/api/product-categories/${id}`, data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }
        return http.put(`/api/product-categories/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/product-categories/${id}`);
    },
};
