<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ isEdit ? 'Редактирование программы' : 'Новая программа' }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-body">
                <div class="field">
                    <label>Название программы *</label>
                    <input v-model="form.name" type="text" required />
                </div>

                <div class="field">
                    <label>Описание</label>
                    <textarea v-model="form.description" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Стоимость за 1 чел. (руб.) *</label>
                        <input v-model="form.price" type="number" min="0" step="0.01" required />
                    </div>
                    <div class="field">
                        <label>Срок обучения</label>
                        <input v-model="form.duration" type="text" placeholder="Например: 40 часов" />
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Требования к образованию</label>
                        <select v-model="form.education_requirement">
                            <option value="none">Не требуется</option>
                            <option value="secondary_professional">Средне-профессиональное</option>
                            <option value="higher">Высшее</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Периодичность повторного обучения *</label>
                        <select v-model="form.retraining_period" required>
                            <option value="none">Не требуется</option>
                            <option value="1_year">1 год</option>
                            <option value="3_years">3 года</option>
                            <option value="5_years">5 лет</option>
                            <option value="custom">Произвольный срок</option>
                        </select>
                    </div>
                </div>

                <div v-if="form.retraining_period === 'custom'" class="field">
                    <label>Количество месяцев *</label>
                    <input v-model="form.custom_months" type="number" min="1" max="120" required />
                </div>

                <div class="row">
                    <div class="field">
                        <label>Статус *</label>
                        <select v-model="form.status" required>
                            <option value="active">Активна</option>
                            <option value="archive">Архив</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Заблаговременность уведомления (дней)</label>
                        <input v-model="form.notify_days_before" type="number" min="1" max="365" />
                    </div>
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
import { reactive, ref } from 'vue';
import { programsApi } from '../api/programs';

const props = defineProps({
    program: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEdit = !!props.program;

const form = reactive({
    name: props.program?.name || '',
    description: props.program?.description || '',
    price: props.program?.price || '',
    duration: props.program?.duration || '',
    education_requirement: props.program?.education_requirement || 'none',
    retraining_period: props.program?.retraining_period || 'none',
    custom_months: props.program?.custom_months || '',
    status: props.program?.status || 'active',
    notify_days_before: props.program?.notify_days_before || 60,
});

const saving = ref(false);
const error = ref('');

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        if (isEdit) {
            await programsApi.update(props.program.id, form);
        } else {
            await programsApi.create(form);
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
</script>

<style scoped>
/* Те же стили, что и в EmployeeForm.vue */
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

.field input, .field select, .field textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
    font-family: inherit;
}

.field input:focus, .field select:focus, .field textarea:focus {
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
</style>