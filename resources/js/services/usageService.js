import http from './http';

export default {
    current() {
        return http.get('/api/usage/current');
    },
    logs(params = {}) {
        return http.get('/api/usage/logs', { params });
    },
    statistics() {
        return http.get('/api/usage/statistics');
    },
};
