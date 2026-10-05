<template>
    <MainLayout>
        <div class="page-header">
            <h1>Уведомления о повторном обучении</h1>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">{{ stats.total || 0 }}</div>
                <div class="stat-label">Всего</div>
            </div>
            <div class="stat-card red">
                <div class="stat-value">{{ stats.overdue || 0 }}</div>
                <div class="stat-label">Просрочено</div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-value">{{ stats.in_60_days || 0 }}</div>
                <div class="stat-label">В течение 60 дней</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value">{{ stats.in_120_days || 0 }}</div>
                <div class="stat-label">60–120 дней</div>
            </div>
        </div>

        <div class="table-wrap">
            <div v-if="loading" class="loading">Загрузка...</div>

            <table v-else class="table">
                <thead>
                    <tr>
                        <th>Сотрудник</th>
                        <th>Организация</th>
                        <th>Программа</th>
                        <th>Прошёл обучение</th>
                        <th>Повторное обучение</th>
                        <th>Дней до/после</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0">
                        <td colspan="6" class="empty">Нет уведомлений</td>
                    </tr>
                    <tr v-for="item in items" :key="item.id">
                        <td>{{ item.employee?.full_name }}</td>
                        <td>{{ item.employee?.organization?.full_name || '—' }}</td>
                        <td>{{ item.program?.name }}</td>
                        <td>{{ item.completed_at }}</td>
                        <td>{{ item.next_training_date }}</td>
                        <td>
                            <span :class="['days', item.urgency_color]">
                                {{ Math.round(item.days_until_retraining) }}
                            </span>     
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import { notificationsApi } from '../api/notifications';

const items = ref([]);
const stats = ref({});
const loading = ref(false);

const loadData = async () => {
    loading.value = true;
    try {
        const [listRes, statsRes] = await Promise.all([
            notificationsApi.list({ per_page: 100 }),
            notificationsApi.stats(),
        ]);
        items.value = listRes.data.data;
        stats.value = statsRes.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(loadData);
</script>

<style scoped>
.page-header { margin-bottom: 20px; }
h1 { font-size: 26px; color: #1e293b; }

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border-left: 4px solid #3b82f6;
}

.stat-card.red { border-left-color: #dc2626; }
.stat-card.yellow { border-left-color: #f59e0b; }
.stat-card.green { border-left-color: #16a34a; }

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 6px;
}

.stat-label {
    color: #64748b;
    font-size: 13px;
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

.days {
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 500;
    font-size: 13px;
}

.days.green { background: #d1fae5; color: #065f46; }
.days.yellow { background: #fef3c7; color: #92400e; }
.days.red { background: #fee2e2; color: #991b1b; }
.days.gray { background: #f1f5f9; color: #64748b; }
</style>