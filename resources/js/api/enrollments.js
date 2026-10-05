import api from './index';

export const enrollmentsApi = {
    list(params = {}) {
        return api.get('/enrollments', { params });
    },
    show(id) {
        return api.get(`/enrollments/${id}`);
    },
    create(data) {
        return api.post('/enrollments', data);
    },
    update(id, data) {
        // PUT с _method=PUT — потому что мы так условились
        return api.post(`/enrollments/${id}`, data, {
            params: { _method: 'PUT' },
        });
    },
    remove(id) {
        return api.delete(`/enrollments/${id}`);
    },
};