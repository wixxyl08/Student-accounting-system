<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ isEdit ? 'Редактирование сотрудника' : 'Новый сотрудник' }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-body">
                <div class="row">
                    <div class="field">
                        <label>Фамилия *</label>
                        <input v-model="form.last_name" type="text" required />
                    </div>
                    <div class="field">
                        <label>Имя *</label>
                        <input v-model="form.first_name" type="text" required />
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Отчество</label>
                        <input v-model="form.middle_name" type="text" />
                    </div>
                    <div class="field">
                        <label>Дата рождения</label>
                        <input v-model="form.birth_date" type="date" />
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Телефон</label>
                        <input v-model="form.phone" type="text" />
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input v-model="form.email" type="email" />
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Организация (если физлицо — оставьте пустым)</label>
                        <select v-model="form.organization_id">
                            <option :value="null">— Не привязан (физлицо) —</option>
                            <option
                                v-for="org in organizations"
                                :key="org.id"
                                :value="org.id"
                            >
                                {{ org.full_name }}
                            </option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Должность</label>
                        <input v-model="form.position" type="text" />
                    </div>
                </div>

                <div class="field">
                    <label>Статус *</label>
                    <select v-model="form.status" required>
                        <option value="active">Активен</option>
                        <option value="fired">Уволен</option>
                    </select>
                </div>

                <div v-if="error" class="error-box">{{ error }}</div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="$emit('close')">Отмена</button>
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        {{ saving ? 'Сохранение...' : 'Сохранить' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { employeesApi } from '../api/employees';
import { organizationsApi } from '../api/organizations';

const props = defineProps({
    employee: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEdit = !!props.employee;

const form = reactive({
    last_name: props.employee?.last_name || '',
    first_name: props.employee?.first_name || '',
    middle_name: props.employee?.middle_name || '',
    birth_date: props.employee?.birth_date || '',
    phone: props.employee?.phone || '',
    email: props.employee?.email || '',
    organization_id: props.employee?.organization_id || null,
    position: props.employee?.position || '',
    status: props.employee?.status || 'active',
});

const saving = ref(false);
const error = ref('');
const organizations = ref([]);

const loadOrganizations = async () => {
    try {
        const { data } = await organizationsApi.list({ per_page: 100 });
        organizations.value = data.data;
    } catch (e) {
        console.error(e);
    }
};

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        if (isEdit) {
            await employeesApi.update(props.employee.id, form);
        } else {
            await employeesApi.create(form);
        }
        emit('saved');
    } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
            error.value = Object.values(errors).flat().join('\n');
        } else {
            error.value = e.response?.data?.message || 'Ошибка сохранения';
        }
    } finally {
        saving.value = false;
    }
};

onMounted(loadOrganizations);
</script>

<style scoped>
/* Те же стили, что в OrganizationForm.vue */
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
    max-width: 700px;
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

h2 { font-size: 20px; color: #1e293b; }

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

.field input, .field select {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.field input:focus, .field select:focus {
    outline: none;
    border-color: #3b82f6;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
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
.btn-secondary:hover { background: #cbd5e1; }
</style>