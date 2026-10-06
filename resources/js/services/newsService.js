import http from './http';

export default {
    list(params = {}) {
        return http.get('/api/news', { params });
    },
    get(id) {
        return http.get(`/api/news/${id}`);
    },
    create(data) {
        return http.post('/api/news', data);
    },
    update(id, data) {
        return http.put(`/api/news/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/news/${id}`);
    },
};
