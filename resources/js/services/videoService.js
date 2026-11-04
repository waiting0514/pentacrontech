import http from './http';

export default {
    list() {
        return http.get('/api/videos');
    },
    create(data) {
        return http.post('/api/videos', data);
    },
    update(id, data) {
        return http.put(`/api/videos/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/videos/${id}`);
    },
};
