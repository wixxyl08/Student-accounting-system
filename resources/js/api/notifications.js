import api from './index';

export const notificationsApi = {
    list(params = {}) {
        return api.get('/notifications', { params });
    },
    stats() {
        return api.get('/notifications/stats');
    },
};