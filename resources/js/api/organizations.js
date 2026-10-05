import api from './index';

export const organizationsApi = {
    list(params = {}) {
        return api.get('/organizations', { params });
    },
    show(id) {
        return api.get(`/organizations/${id}`);
    },
    create(data) {
        return api.post('/organizations', data);
    },
    update(id, data) {
        return api.put(`/organizations/${id}`, data);
    },
    remove(id) {
        return api.delete(`/organizations/${id}`);
    },
};