<template>
    <MainLayout>
        <div class="page-header">
            <h1>Управление пользователями</h1>
        </div>

        <DataTable
            :columns="columns"
            :rows="users"
            :meta="meta"
            :loading="loading"
            @search="handleSearch"
            @page="handlePage"
            @create="handleCreate"
            @edit="handleEdit"
            @delete="handleDelete"
        >
            <template #row-actions="{ row }">
                <button
                    class="btn-icon"
                    @click="toggleBlock(row)"
                    :title="row.is_blocked ? 'Разблокировать' : 'Заблокировать'"
                >
                    {{ row.is_blocked ? '🔓' : '🔒' }}
                </button>
            </template>
        </DataTable>

        <UserForm
            v-if="showForm"
            :user="editingUser"
            @close="closeForm"
            @saved="handleSaved"
        />
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import UserForm from './UserForm.vue';
import { usersApi } from '../api/users';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'full_name', label: 'ФИО' },
    { key: 'login', label: 'Логин' },
    { key: 'role_name', label: 'Роль' },
    { key: 'last_login_at', label: 'Последний вход' },
];

const users = ref([]);
const meta = ref(null);
const loading = ref(false);
const search = ref('');
const page = ref(1);

const showForm = ref(false);
const editingUser = ref(null);

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await usersApi.list({
            search: search.value,
            page: page.value,
            per_page: 10,
        });
        users.value = data.data.map(u => ({
            ...u,
            is_blocked_label: u.is_blocked ? 'Заблокирован' : 'Активен',
        }));
        meta.value = data.meta;
    } catch (e) {
        console.error(e);
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
    editingUser.value = null;
    showForm.value = true;
};

const handleEdit = (row) => {
    editingUser.value = row;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingUser.value = null;
};

const handleSaved = () => {
    closeForm();
    loadData();
};

const toggleBlock = async (row) => {
    try {
        const { data } = await usersApi.toggleBlock(row.id);
        loadData();
    } catch (e) {
        alert(e.response?.data?.message || 'Ошибка');
    }
};

const handleDelete = async (row) => {
    if (!confirm(`Удалить пользователя «${row.full_name}»?`)) return;
    try {
        await usersApi.remove(row.id);
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

.btn-icon {
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 14px;
    margin-right: 4px;
}

.btn-icon:hover { background: #f1f5f9; }
</style>