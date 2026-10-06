import http from './http';

export default {
    list(params = {}) {
        return http.get('/api/admin/orders', { params });
    },
    get(id) {
        return http.get(`/api/admin/orders/${id}`);
    },
    updateStatus(id, status) {
        return http.patch(`/api/admin/orders/${id}/status`, { status });
    },
};
