import http from './http';

export default {
    get() {
        return http.get('/api/settings');
    },
    update(data) {
        return http.put('/api/settings', data);
    },
    testSmtp() {
        return http.post('/api/settings/test-smtp');
    },
};
