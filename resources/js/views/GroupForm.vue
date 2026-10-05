<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ isEdit ? 'Редактирование группы' : 'Новая группа' }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-body">
                <div class="field">
                    <label>Название группы</label>
                    <input v-model="form.name" type="text" placeholder="Оставьте пустым для автоназвания" />
                </div>

                <div class="field">
                    <label>Программа обучения *</label>
                    <select v-model="form.program_id" required>
                        <option :value="null" disabled>— Выберите программу —</option>
                        <option v-for="p in programs" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Дата начала *</label>
                        <input v-model="form.start_date" type="date" required />
                    </div>
                    <div class="field">
                        <label>Дата окончания</label>
                        <input v-model="form.end_date" type="date" />
                    </div>
                </div>

                <div class="field">
                    <label>Статус *</label>
                    <select v-model="form.status" required>
                        <option value="recruiting">Набор</option>
                        <option value="ongoing">Идёт обучение</option>
                        <option value="finished">Завершена</option>
                        <option value="cancelled">Отменена</option>
                    </select>
                </div>

                <div class="field">
                    <label>Примечание</label>
                    <textarea v-model="form.note" rows="3"></textarea>
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
import { groupsApi } from '../api/groups';
import { programsApi } from '../api/programs';

const props = defineProps({
    group: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEdit = !!props.group;

const form = reactive({
    name: props.group?.name || '',
    program_id: props.group?.program_id || null,
    start_date: props.group?.start_date || '',
    end_date: props.group?.end_date || '',
    status: props.group?.status || 'recruiting',
    note: props.group?.note || '',
});

const saving = ref(false);
const error = ref('');
const programs = ref([]);

const loadPrograms = async () => {
    try {
        const { data } = await programsApi.list({ status: 'active', per_page: 100 });
        programs.value = data.data;
    } catch (e) {
        console.error(e);
    }
};

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        if (isEdit) {
            await groupsApi.update(props.group.id, form);
        } else {
            await groupsApi.create(form);
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

onMounted(loadPrograms);
</script>

<style scoped>
/* Те же стили, что и в предыдущих формах */
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