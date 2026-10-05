import api from './index';

export const groupsApi = {
    list(params = {}) {
        return api.get('/groups', { params });
    },
    show(id) {
        return api.get(`/groups/${id}`);
    },
    create(data) {
        return api.post('/groups', data);
    },
    update(id, data) {
        return api.put(`/groups/${id}`, data);
    },
    remove(id) {
        return api.delete(`/groups/${id}`);
    },
};