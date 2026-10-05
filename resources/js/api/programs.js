import api from './index';

export const programsApi = {
    list(params = {}) {
        return api.get('/programs', { params });
    },
    show(id) {
        return api.get(`/programs/${id}`);
    },
    create(data) {
        return api.post('/programs', data);
    },
    update(id, data) {
        return api.put(`/programs/${id}`, data);
    },
    remove(id) {
        return api.delete(`/programs/${id}`);
    },
};