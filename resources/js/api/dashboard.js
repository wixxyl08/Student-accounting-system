import api from './index';

export const dashboardApi = {
    get() {
        return api.get('/dashboard');
    },
};