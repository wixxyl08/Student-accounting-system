<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>Состав группы: {{ group.name }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <div class="modal-body">
                <button class="btn btn-primary add-btn" @click="openForm">
                    + Зачислить сотрудника
                </button>

                <div v-if="loading" class="loading">Загрузка...</div>

                <table v-else-if="enrollments.length" class="table">
                    <thead>
                        <tr>
                            <th>ФИО</th>
                            <th>Организация</th>
                            <th>Статус</th>
                            <th>Удостоверение</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="enr in enrollments" :key="enr.id">
                            <td>{{ enr.employee?.full_name || '—' }}</td>
                            <td>{{ enr.employee?.organization?.full_name || '—' }}</td>
                            <td>
                                <span :class="['status', enr.status]">{{ enr.status_name }}</span>
                            </td>
                            <td>{{ enr.certificate_number || '—' }}</td>
                            <td>
                                <select v-model="enr.status" @change="changeStatus(enr)">
                                    <option value="enrolled">Зачислен</option>
                                    <option value="studying">Обучается</option>
                                    <option value="completed">Прошёл обучение</option>
                                    <option value="expelled">Отчислен</option>
                                </select>
                                <button class="btn-icon" @click="removeEnrollment(enr)">🗑️</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="empty">Нет зачисленных</div>
            </div>
        </div>

        <!-- Форма зачисления -->
        <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
            <div class="modal small">
                <div class="modal-header">
                    <h2>Зачислить сотрудника</h2>
                    <button class="close-btn" @click="closeForm">✕</button>
                </div>

                <form @submit.prevent="handleSubmit" class="modal-body">
                    <div class="field">
                        <label>Сотрудник *</label>
                        <select v-model="form.employee_id" required>
                            <option :value="null" disabled>— Выберите сотрудника —</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.full_name }}
                            </option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Номер удостоверения</label>
                        <input v-model="form.certificate_number" type="text" />
                    </div>

                    <div class="field">
                        <label>Примечание</label>
                        <textarea v-model="form.note" rows="2"></textarea>
                    </div>

                    <div v-if="error" class="error-box">{{ error }}</div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeForm">Отмена</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Зачисление...' : 'Зачислить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { enrollmentsApi } from '../api/enrollments';
import { employeesApi } from '../api/employees';

const props = defineProps({
    group: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const enrollments = ref([]);
const employees = ref([]);
const loading = ref(false);

const showForm = ref(false);
const form = reactive({
    employee_id: null,
    certificate_number: '',
    note: '',
});
const saving = ref(false);
const error = ref('');

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await enrollmentsApi.list({
            group_id: props.group.id,
            per_page: 100,
        });
        enrollments.value = data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const loadEmployees = async () => {
    try {
        const { data } = await employeesApi.list({ status: 'active', per_page: 200 });
        employees.value = data.data;
    } catch (e) {
        console.error(e);
    }
};

const openForm = () => {
    Object.assign(form, { employee_id: null, certificate_number: '', note: '' });
    error.value = '';
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
};

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        await enrollmentsApi.create({
            employee_id: form.employee_id,
            group_id: props.group.id,
            certificate_number: form.certificate_number || null,
            note: form.note || null,
        });
        closeForm();
        loadData();
    } catch (e) {
        error.value = e.response?.data?.message || 'Ошибка зачисления';
    } finally {
        saving.value = false;
    }
};

const changeStatus = async (enr) => {
    try {
        await enrollmentsApi.update(enr.id, { status: enr.status });
        loadData();
    } catch (e) {
        alert(e.response?.data?.message || 'Ошибка смены статуса');
    }
};

const removeEnrollment = async (enr) => {
    if (!confirm(`Удалить зачисление «${enr.employee?.full_name}»?`)) return;
    try {
        await enrollmentsApi.remove(enr.id);
        loadData();
    } catch (e) {
        alert('Ошибка удаления');
    }
};

onMounted(() => {
    loadData();
    loadEmployees();
});
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    overflow-y: auto;
    padding: 20px;
}

.modal {
    background: #fff;
    border-radius: 10px;
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal.small { max-width: 600px; }

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

h2 { font-size: 20px; color: #1e293b; }

.close-btn {
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #64748b;
}

.modal-body { padding: 24px; }

.add-btn { margin-bottom: 20px; }

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

.status {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status.enrolled { background: #dbeafe; color: #1e40af; }
.status.studying { background: #fef3c7; color: #92400e; }
.status.completed { background: #d1fae5; color: #065f46; }
.status.expelled { background: #fee2e2; color: #991b1b; }

.field { margin-bottom: 16px; }

.field label {
    display: block;
    font-size: 13px;
    color: #475569;
    margin-bottom: 6px;
}

.field input, .field select, .field textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
    font-family: inherit;
}

.empty {
    text-align: center;
    color: #94a3b8;
    padding: 40px;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #94a3b8;
}

.error-box {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 16px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    margin-top: 16px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary { background: #3b82f6; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #2563eb; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #e2e8f0; color: #1e293b; }

.btn-icon {
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 14px;
    margin-left: 6px;
}

.btn-icon:hover { background: #f1f5f9; }
</style>