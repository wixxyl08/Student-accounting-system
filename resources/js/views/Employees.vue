<template>
    <MainLayout>
        <div class="page-header">
            <h1>Сотрудники</h1>
        </div>

        <DataTable
            :columns="columns"
            :rows="employees"
            :meta="meta"
            :loading="loading"
            @search="handleSearch"
            @page="handlePage"
            @create="handleCreate"
            @edit="handleEdit"
            @delete="handleDelete"
        >
            <template #row-actions="{ row }">
                <button class="btn-icon" @click="openEducation(row)" title="Образование">🎓</button>
                </template>
        </DataTable>

<EmployeeForm
    v-if="showForm"
    :employee="editingEmployee"
    @close="closeForm"
    @saved="handleSaved"
/>

<EducationModal
    v-if="showEducation && educationEmployee"
    :employee="educationEmployee"
    @close="closeEducation"
/>
    </MainLayout>
</template>

<script setup>
import EducationModal from './EducationModal.vue';
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import EmployeeForm from './EmployeeForm.vue';
import { employeesApi } from '../api/employees';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'full_name', label: 'ФИО' },
    { key: 'position', label: 'Должность' },
    { key: 'phone', label: 'Телефон' },
    { key: 'email', label: 'Email' },
];

const employees = ref([]);
const meta = ref(null);
const loading = ref(false);
const search = ref('');
const page = ref(1);

const showForm = ref(false);
const editingEmployee = ref(null);
const showEducation = ref(false);
const educationEmployee = ref(null);

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await employeesApi.list({
            search: search.value,
            page: page.value,
            per_page: 10,
        });
        employees.value = data.data;
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
    editingEmployee.value = null;
    showForm.value = true;
};

const handleEdit = (row) => {
    editingEmployee.value = row;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingEmployee.value = null;
};

const handleSaved = () => {
    closeForm();
    loadData();
};

const openEducation = (row) => {
    educationEmployee.value = row;
    showEducation.value = true;
};

const closeEducation = () => {
    showEducation.value = false;
    educationEmployee.value = null;
};

const handleDelete = async (row) => {
    if (!confirm(`Удалить «${row.full_name}»?`)) return;
    try {
        await employeesApi.remove(row.id);
        loadData();
    } catch (e) {
        alert(e.response?.data?.message || 'Ошибка удаления');
    }
};

onMounted(loadData);
</script>

<style scoped>
.page-header { margin-bottom: 20px; }
h1 { font-size: 26px; color: #1e293b; }
</style>