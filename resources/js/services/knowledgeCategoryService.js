import http from './http';

export default {
    list() {
        return http.get('/api/knowledge-categories');
    },
    create(data) {
        return http.post('/api/knowledge-categories', data);
    },
    update(id, data) {
        return http.put(`/api/knowledge-categories/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/knowledge-categories/${id}`);
    },
};
