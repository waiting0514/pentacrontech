import http from './http';

export default {
    uploadImage(formData) {
        return http.post('/api/upload-image', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
};
