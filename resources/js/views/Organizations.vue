<template>
    <MainLayout>
        <div class="page-header">
            <h1>Организации</h1>
        </div>

        <DataTable
            :columns="columns"
            :rows="organizations"
            :meta="meta"
            :loading="loading"
            @search="handleSearch"
            @page="handlePage"
            @create="handleCreate"
            @edit="handleEdit"
            @delete="handleDelete"
        />

        <OrganizationForm
            v-if="showForm"
            :organization="editingOrganization"
            @close="closeForm"
            @saved="handleSaved"
        />
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import OrganizationForm from './OrganizationForm.vue';
import { organizationsApi } from '../api/organizations';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'full_name', label: 'Наименование' },
    { key: 'inn', label: 'ИНН' },
    { key: 'phone', label: 'Телефон' },
    { key: 'email', label: 'Email' },
    { key: 'contact_person', label: 'Контактное лицо' },
];

const organizations = ref([]);
const meta = ref(null);
const loading = ref(false);
const search = ref('');
const page = ref(1);

const showForm = ref(false);
const editingOrganization = ref(null);

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await organizationsApi.list({
            search: search.value,
            page: page.value,
            per_page: 10,
        });
        organizations.value = data.data;
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
    editingOrganization.value = null;
    showForm.value = true;
};

const handleEdit = (row) => {
    editingOrganization.value = row;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingOrganization.value = null;
};

const handleSaved = () => {
    closeForm();
    loadData();
};

const handleDelete = async (row) => {
    if (!confirm(`Удалить «${row.full_name}»?`)) return;
    try {
        await organizationsApi.remove(row.id);
        loadData();
    } catch (e) {
        alert(e.response?.data?.message || 'Ошибка удаления');
    }
};

onMounted(loadData);
</script>

<style scoped>
.page-header {
    margin-bottom: 20px;
}

h1 {
    font-size: 26px;
    color: #1e293b;
}
</style>