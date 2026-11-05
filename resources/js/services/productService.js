import http from './http';

function buildAttachmentFormData({ titles, files, sortOrder } = {}) {
    const fd = new FormData();
    if (titles) {
        Object.entries(titles).forEach(([locale, value]) => {
            fd.append(`title[${locale}]`, value ?? '');
        });
    }
    if (files) {
        Object.entries(files).forEach(([locale, file]) => {
            if (file) fd.append(`files[${locale}]`, file);
        });
    }
    if (sortOrder !== undefined && sortOrder !== null && sortOrder !== '') {
        fd.append('sort_order', sortOrder);
    }
    return fd;
}

export default {
    list(params = {}) {
        return http.get('/api/products', { params });
    },
    get(id) {
        return http.get(`/api/products/${id}`);
    },
    create(data) {
        return http.post('/api/products', data);
    },
    update(id, data) {
        return http.put(`/api/products/${id}`, data);
    },
    delete(id) {
        return http.delete(`/api/products/${id}`);
    },
    uploadImages(productId, formData) {
        return http.post(`/api/products/${productId}/images`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    deleteImage(productId, imageId) {
        return http.delete(`/api/products/${productId}/images/${imageId}`);
    },
    setPrimaryImage(productId, imageId) {
        return http.put(`/api/products/${productId}/images/${imageId}/primary`);
    },
    // 附件（PDF）。payload: { titles: {locale: str}, files: {locale: File}, sortOrder }
    listAttachments(productId) {
        return http.get(`/api/products/${productId}/attachments`);
    },
    createAttachment(productId, payload) {
        return http.post(`/api/products/${productId}/attachments`, buildAttachmentFormData(payload), {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    updateAttachment(productId, attachmentId, payload) {
        return http.post(`/api/products/${productId}/attachments/${attachmentId}`, buildAttachmentFormData(payload), {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    deleteAttachment(productId, attachmentId) {
        return http.delete(`/api/products/${productId}/attachments/${attachmentId}`);
    },
    deleteAttachmentFile(productId, attachmentId, locale) {
        return http.delete(`/api/products/${productId}/attachments/${attachmentId}/files/${encodeURIComponent(locale)}`);
    },
    batchAction(data) {
        return http.post('/api/products/batch-action', data);
    },
};
