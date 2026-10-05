import api from './index';

export const usersApi = {
    list(params = {}) {
        return api.get('/admin/users', { params });
    },
    show(id) {
        return api.get(`/admin/users/${id}`);
    },
    create(data) {
        return api.post('/admin/users', data);
    },
    update(id, data) {
        return api.put(`/admin/users/${id}`, data);
    },
    remove(id) {
        return api.delete(`/admin/users/${id}`);
    },
    toggleBlock(id) {
        return api.post(`/admin/users/${id}/toggle-block`);
    },
};