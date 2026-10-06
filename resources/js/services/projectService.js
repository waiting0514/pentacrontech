import http from './http';

export default {
    list() {
        return http.get('/api/projects');
    },
    get(id) {
        return http.get(`/api/projects/${id}`);
    },
    create(data) {
        return http.post('/api/projects', data);
    },
    update(id, data) {
        return http.put(`/api/projects/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/projects/${id}`);
    },
    current() {
        return http.get('/api/projects/current');
    },
    switch(id) {
        return http.post(`/api/projects/${id}/switch`);
    },
    statistics(id) {
        return http.get(`/api/projects/${id}/statistics`);
    },
    testSmtp(id) {
        return http.post(`/api/projects/${id}/test-smtp`);
    },
};
