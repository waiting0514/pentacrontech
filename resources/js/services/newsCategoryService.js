import http from './http';

export default {
    list() {
        return http.get('/api/news-categories');
    },
    create(data) {
        return http.post('/api/news-categories', data);
    },
    update(id, data) {
        return http.put(`/api/news-categories/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/news-categories/${id}`);
    },
};
