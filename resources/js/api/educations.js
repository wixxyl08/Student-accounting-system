import api from './index';

export const educationsApi = {
    list(params = {}) {
        return api.get('/educations', { params });
    },
    show(id) {
        return api.get(`/educations/${id}`);
    },
    create(formData) {
        return api.post('/educations', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    update(id, formData) {
        return api.post(`/educations/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            params: { _method: 'PUT' },
        });
    },
    remove(id) {
        return api.delete(`/educations/${id}`);
    },
};