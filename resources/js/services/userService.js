import http from './http';

export default {
    list() {
        return http.get('/api/users');
    },
    create(data) {
        return http.post('/api/users', data);
    },
    update(id, data) {
        return http.put(`/api/users/${id}`, data);
    },
    toggleStatus(id) {
        return http.put(`/api/users/${id}/toggle`);
    },
    destroy(id) {
        return http.delete(`/api/users/${id}`);
    },
};
