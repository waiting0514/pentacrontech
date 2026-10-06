import http from './http';

export default {
    login(credentials) {
        return http.post('/api/login', credentials);
    },
    register(data) {
        return http.post('/api/register', data);
    },
    logout() {
        return http.post('/api/logout');
    },
    user() {
        return http.get('/api/user');
    },
};
