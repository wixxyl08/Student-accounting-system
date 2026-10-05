<template>
    <MainLayout>
        <div class="page-header">
            <h1>Реестр договоров</h1>
        </div>

        <DataTable
            :columns="columns"
            :rows="contracts"
            :meta="meta"
            :loading="loading"
            @search="handleSearch"
            @page="handlePage"
            @delete="handleDelete"
        >
            <template #row-actions="{ row }">
                <button class="btn-icon" @click="downloadDocx(row)" title="Скачать DOCX">📄</button>
                <button class="btn-icon" @click="downloadPdf(row)" title="Скачать PDF">📕</button>
            </template>
        </DataTable>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import { contractsApi } from '../api/contracts';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'number', label: 'Номер' },
    { key: 'customer_name', label: 'Заказчик' },
    { key: 'total_amount', label: 'Сумма' },
    { key: 'created_at', label: 'Дата' },
];

const contracts = ref([]);
const meta = ref(null);
const loading = ref(false);
const search = ref('');
const page = ref(1);

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await contractsApi.list({
            search: search.value,
            page: page.value,
            per_page: 10,
        });
        contracts.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        console.error(e);
        alert('Ошибка загрузки');
    } finally {
        loading.value = false;
    }
};

const handleSearch = (value) => {
    search.value = value;
    page.value = 1;
    loadData();
};

const handlePage = (newPage) => {
    page.value = newPage;
    loadData();
};

const downloadFile = (response, filename) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
};

const downloadDocx = async (row) => {
    try {
        const response = await contractsApi.downloadDocx(row.id);
        downloadFile(response, `${row.number}.docx`);
    } catch (e) {
        alert('Ошибка скачивания DOCX');
    }
};

const downloadPdf = async (row) => {
    try {
        const response = await contractsApi.downloadPdf(row.id);
        downloadFile(response, `${row.number}.pdf`);
    } catch (e) {
        alert('Ошибка скачивания PDF');
    }
};

const handleDelete = async (row) => {
    if (!confirm(`Удалить договор ${row.number}?`)) return;
    try {
        await contractsApi.remove(row.id);
        loadData();
    } catch (e) {
        alert('Ошибка удаления');
    }
};

onMounted(loadData);
</script>

<style scoped>
.page-header { margin-bottom: 20px; }
h1 { font-size: 26px; color: #1e293b; }

.btn-icon {
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 14px;
    margin-right: 4px;
}

.btn-icon:hover {
    background: #f1f5f9;
}
</style>