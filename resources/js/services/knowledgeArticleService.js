import http from './http';

export default {
    list(params = {}) {
        return http.get('/api/knowledge-articles', { params });
    },
    get(id) {
        return http.get(`/api/knowledge-articles/${id}`);
    },
    create(data) {
        return http.post('/api/knowledge-articles', data);
    },
    update(id, data) {
        return http.put(`/api/knowledge-articles/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/knowledge-articles/${id}`);
    },
};
