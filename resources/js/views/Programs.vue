<template>
    <MainLayout>
        <div class="page-header">
            <h1>Программы обучения</h1>
        </div>

        <DataTable
            :columns="columns"
            :rows="programs"
            :meta="meta"
            :loading="loading"
            @search="handleSearch"
            @page="handlePage"
            @create="handleCreate"
            @edit="handleEdit"
            @delete="handleDelete"
        />

        <ProgramForm
            v-if="showForm"
            :program="editingProgram"
            @close="closeForm"
            @saved="handleSaved"
        />
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import ProgramForm from './ProgramForm.vue';
import { programsApi } from '../api/programs';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Название' },
    { key: 'price', label: 'Цена' },
    { key: 'retraining_period_name', label: 'Периодичность' },
    { key: 'status', label: 'Статус' },
];

const programs = ref([]);
const meta = ref(null);
const loading = ref(false);
const search = ref('');
const page = ref(1);

const showForm = ref(false);
const editingProgram = ref(null);

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await programsApi.list({
            search: search.value,
            page: page.value,
            per_page: 10,
        });
        programs.value = data.data;
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

const handleCreate = () => {
    editingProgram.value = null;
    showForm.value = true;
};

const handleEdit = (row) => {
    editingProgram.value = row;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingProgram.value = null;
};

const handleSaved = () => {
    closeForm();
    loadData();
};

const handleDelete = async (row) => {
    if (!confirm(`Архивировать «${row.name}»?`)) return;
    try {
        await programsApi.remove(row.id);
        loadData();
    } catch (e) {
        alert(e.response?.data?.message || 'Ошибка');
    }
};

onMounted(loadData);
</script>

<style scoped>
.page-header { margin-bottom: 20px; }
h1 { font-size: 26px; color: #1e293b; }
</style>