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
            @create="openForm"
            @delete="handleDelete"
        >
            <template #row-actions="{ row }">
                <button class="btn-icon" @click="downloadDocx(row)" title="Скачать DOCX">📄</button>
                <button class="btn-icon" @click="downloadPdf(row)" title="Скачать PDF">📕</button>
            </template>
        </DataTable>

        <!-- Модальное окно создания договора -->
        <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
            <div class="modal">
                <div class="modal-header">
                    <h2>Новый договор</h2>
                    <button class="close-btn" @click="closeForm">✕</button>
                </div>

                <form @submit.prevent="handleSubmit" class="modal-body">
                    <div class="field">
                        <label>Группа обучения *</label>
                        <select v-model="form.group_id" required>
                            <option :value="null" disabled>— Выберите группу —</option>
                            <option v-for="g in groups" :key="g.id" :value="g.id">
                                {{ g.name }}
                            </option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Программа обучения *</label>
                        <select v-model="form.program_id" required>
                            <option :value="null" disabled>— Выберите программу —</option>
                            <option v-for="p in programs" :key="p.id" :value="p.id">
                                {{ p.name }} — {{ p.price }} ₽
                            </option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Заказчик *</label>
                        <select v-model="form.customer_type" required>
                            <option value="organization">Организация</option>
                            <option value="employee">Физическое лицо</option>
                        </select>
                    </div>

                    <div v-if="form.customer_type === 'organization'" class="field">
                        <label>Организация *</label>
                        <select v-model="form.organization_id" required>
                            <option :value="null" disabled>— Выберите организацию —</option>
                            <option v-for="o in organizations" :key="o.id" :value="o.id">
                                {{ o.full_name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="form.customer_type === 'employee'" class="field">
                        <label>Физическое лицо *</label>
                        <select v-model="form.employee_id" required>
                            <option :value="null" disabled>— Выберите сотрудника —</option>
                            <option v-for="e in employees" :key="e.id" :value="e.id">
                                {{ e.full_name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="error" class="error-box">{{ error }}</div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeForm">Отмена</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Формирование...' : 'Сформировать договор' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import MainLayout from '../layouts/MainLayout.vue';
import DataTable from '../components/DataTable.vue';
import { contractsApi } from '../api/contracts';
import { groupsApi } from '../api/groups';
import { programsApi } from '../api/programs';
import { organizationsApi } from '../api/organizations';
import { employeesApi } from '../api/employees';

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

// Для модального окна
const showForm = ref(false);
const saving = ref(false);
const error = ref('');

const groups = ref([]);
const programs = ref([]);
const organizations = ref([]);
const employees = ref([]);

const form = reactive({
    group_id: null,
    program_id: null,
    customer_type: 'organization',
    organization_id: null,
    employee_id: null,
});

// === Загрузка данных ===

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

const loadSelects = async () => {
    try {
        const [g, p, o, e] = await Promise.all([
            groupsApi.list({ per_page: 100 }),
            programsApi.list({ per_page: 100, status: 'active' }),
            organizationsApi.list({ per_page: 100 }),
            employeesApi.list({ per_page: 100, status: 'active' }),
        ]);
        groups.value = g.data.data;
        programs.value = p.data.data;
        organizations.value = o.data.data;
        employees.value = e.data.data;
    } catch (e) {
        console.error(e);
    }
};

// === Обработчики ===

const handleSearch = (value) => {
    search.value = value;
    page.value = 1;
    loadData();
};

const handlePage = (newPage) => {
    page.value = newPage;
    loadData();
};

const openForm = () => {
    form.group_id = null;
    form.program_id = null;
    form.customer_type = 'organization';
    form.organization_id = null;
    form.employee_id = null;
    error.value = '';
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    error.value = '';
};

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        const payload = {
            group_id: form.group_id,
            program_id: form.program_id,
            organization_id: form.customer_type === 'organization' ? form.organization_id : null,
            employee_id: form.customer_type === 'employee' ? form.employee_id : null,
        };
        await contractsApi.create(payload);
        closeForm();
        loadData();
    } catch (e) {
        console.error('❌ Ошибка:', e);
        console.error('📦 Ответ от сервера:', e.response);
        console.error('📤 Отправленный запрос:', e.request);
        console.error('💬 Текст ошибки:', e.message);
        const errors = e.response?.data?.errors;
        if (errors) {
            error.value = Object.values(errors).flat().join('\n');
        } else {
            error.value = e.response?.data?.message || ('Ошибка: ' + e.message);
        }
    } finally {
        saving.value = false;
    }
};

// === Скачивание ===

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

onMounted(() => {
    loadData();
    loadSelects();
});
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

/* Стили модального окна */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal {
    background: #fff;
    border-radius: 10px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 { font-size: 20px; color: #1e293b; }

.close-btn {
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #64748b;
}

.modal-body { padding: 24px; }

.field { margin-bottom: 16px; }

.field label {
    display: block;
    font-size: 13px;
    color: #475569;
    margin-bottom: 6px;
}

.field select {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.error-box {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 16px;
    white-space: pre-line;
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
</style>