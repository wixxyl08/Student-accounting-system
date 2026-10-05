import api from './index';

export const activityLogsApi = {
    list(params = {}) {
        return api.get('/admin/activity-logs', { params });
    },
};