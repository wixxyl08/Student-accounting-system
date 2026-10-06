<template>
    <MainLayout>
        <div class="page-header">
            <h1>Журнал действий</h1>
        </div>

        <div class="filters">
            <select v-model="filterAction" @change="loadData">
                <option value="">Все действия</option>
                <option value="created">Создание</option>
                <option value="updated">Изменение</option>
                <option value="deleted">Удаление</option>
            </select>
        </div>

        <div class="table-wrap">
            <div v-if="loading" class="loading">Загрузка...</div>

            <table v-else class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Сущность</th>
                        <th>Объект ID</th>
                        <th>Действие</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="logs.length === 0">
                        <td colspan="6" class="empty">Нет записей</td>
                    </tr>
                    <tr v-for="log in logs" :key="log.id">
                        <td>{{ log.id }}</td>
                        <td>{{ log.user?.full_name || '—' }}</td>
                        <td>{{ shortType(log.entity_type) }}</td>
                        <td>{{ log.entity_id }}</td>
                        <td>
                            <span :class="['action', log.action]">{{ actionName(log.action) }}</span>
                        </td>
                        <td>{{ log.created_at }}</td>
                    </tr>
                </tbody>
            </table>

            <div v-if="meta && meta.last_page > 1" class="pagination">
                <button :disabled="meta.current_page === 1" @click="changePage(meta.current_page - 1)">←</button>
                <span>Стр. {{ meta.current_page }} из {{ meta.last_page }}</span>
                <button :disabled="meta.current_page === meta.last_page" @click="changePage(meta.current_page + 1)">→</button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import { activityLogsApi } from '../api/activity_logs';

const logs = ref([]);
const meta = ref(null);
const loading = ref(false);
const page = ref(1);
const filterAction = ref('');

const shortType = (type) => {
    if (!type) return '—';
    return type.split('\\').pop();
};

const actionName = (action) => {
    return {
        created: 'Создано',
        updated: 'Изменено',
        deleted: 'Удалено',
    }[action] || action;
};

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await activityLogsApi.list({
            page: page.value,
            action: filterAction.value || undefined,
            per_page: 20,
        });
        logs.value = data.data;
        // Laravel отдаёт meta прямо в корне ответа
        meta.value = {
            current_page: data.current_page,
            last_page:    data.last_page,
            per_page:     data.per_page,
            total:        data.total,
            from:         data.from,
            to:           data.to,
        };
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const changePage = (newPage) => {
    page.value = newPage;
    loadData();
};

onMounted(loadData);
</script>

<style scoped>
.page-header { margin-bottom: 20px; }
h1 { font-size: 26px; color: #1e293b; }

.filters {
    margin-bottom: 20px;
}

.filters select {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
}

.table-wrap {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    padding: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
}

.table th {
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
    text-transform: uppercase;
}

.action {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.action.created { background: #d1fae5; color: #065f46; }
.action.updated { background: #dbeafe; color: #1e40af; }
.action.deleted { background: #fee2e2; color: #991b1b; }

.empty {
    text-align: center;
    color: #94a3b8;
    padding: 40px;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #94a3b8;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    margin-top: 20px;
}

.pagination button {
    padding: 8px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
}

.pagination button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>