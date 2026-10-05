import api from './index';

export const employeesApi = {
    list(params = {}) {
        return api.get('/employees', { params });
    },
    show(id) {
        return api.get(`/employees/${id}`);
    },
    create(data) {
        return api.post('/employees', data);
    },
    update(id, data) {
        return api.put(`/employees/${id}`, data);
    },
    remove(id) {
        return api.delete(`/employees/${id}`);
    },
};