import http from './http';

export default {
    list() {
        return http.get('/api/banners');
    },
    create(formData) {
        return http.post('/api/banners', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    update(id, formData) {
        return http.post(`/api/banners/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    updateSort(id, sortOrder) {
        return http.patch(`/api/banners/${id}/sort`, { sort_order: sortOrder });
    },
    delete(id) {
        return http.delete(`/api/banners/${id}`);
    },
};
