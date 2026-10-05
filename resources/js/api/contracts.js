import api from './index';

export const contractsApi = {
    list(params = {}) {
        return api.get('/contracts', { params });
    },
    show(id) {
        return api.get(`/contracts/${id}`);
    },
    remove(id) {
        return api.delete(`/contracts/${id}`);
    },
    downloadDocx(id) {
        return api.get(`/contracts/${id}/download-docx`, {
            responseType: 'blob',
        });
    },
    downloadPdf(id) {
        return api.get(`/contracts/${id}/download-pdf`, {
            responseType: 'blob',
        });
    },
};