<template>
    <MainLayout>
        <div class="page-header">
            <h1>Группы обучения</h1>
        </div>

        <DataTable
            :columns="columns"
            :rows="groups"
            :meta="meta"
            :loading="loading"
            @search="handleSearch"
            @page="handlePage"
            @create="handleCreate"
            @edit="handleEdit"
            @delete="handleDelete"
        >
            <template #row-actions="{ row }">
                <button class="btn-icon" @click="openEnrollments(row)" title="Состав группы">👥</button>
            </template>
        </DataTable>

        <GroupForm
            v-if="showForm"
            :group="editingGroup"
            @close="closeForm"
            @saved="handleSaved"
        />
        <EnrollmentModal
            v-if="showEnrollments && enrollmentGroup"
            :group="enrollmentGroup"
            @close="closeEnrollments"
        />
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import GroupForm from './GroupForm.vue';
import EnrollmentModal from './EnrollmentModal.vue';
import { groupsApi } from '../api/groups';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Название' },
    { key: 'start_date', label: 'Дата начала' },
    { key: 'status_name', label: 'Статус' },
    { key: 'enrollments_count', label: 'Обучающихся' },
];

const groups = ref([]);
const meta = ref(null);
const loading = ref(false);
const search = ref('');
const page = ref(1);

const showForm = ref(false);
const editingGroup = ref(null);
const showEnrollments = ref(false);
const enrollmentGroup = ref(null);

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await groupsApi.list({
            search: search.value,
            page: page.value,
            per_page: 10,
        });
        groups.value = data.data;
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
    editingGroup.value = null;
    showForm.value = true;
};

const handleEdit = (row) => {
    editingGroup.value = row;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingGroup.value = null;
};

const handleSaved = () => {
    closeForm();
    loadData();
};

const openEnrollments = (row) => {
    enrollmentGroup.value = row;
    showEnrollments.value = true;
};

const closeEnrollments = () => {
    showEnrollments.value = false;
    enrollmentGroup.value = null;
};

const handleDelete = async (row) => {
    if (!confirm(`Удалить группу «${row.name}»?`)) return;
    try {
        await groupsApi.remove(row.id);
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