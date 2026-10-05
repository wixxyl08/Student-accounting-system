<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ isEdit ? 'Редактирование пользователя' : 'Новый пользователь' }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-body">
                <div class="field">
                    <label>ФИО *</label>
                    <input v-model="form.full_name" type="text" required />
                </div>

                <div class="field">
                    <label>Логин *</label>
                    <input v-model="form.login" type="text" required />
                </div>

                <div class="field">
                    <label>Пароль {{ isEdit ? '(оставьте пустым, чтобы не менять)' : '*' }}</label>
                    <input v-model="form.password" type="password" :required="!isEdit" />
                </div>

                <div class="field">
                    <label>Роль *</label>
                    <select v-model="form.role" required>
                        <option value="methodist">Методист</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>

                <div v-if="isEdit" class="field checkbox">
                    <label>
                        <input v-model="form.is_blocked" type="checkbox" />
                        Заблокирован
                    </label>
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
import { usersApi } from '../api/users';

const props = defineProps({
    user: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEdit = !!props.user;

const form = reactive({
    full_name: props.user?.full_name || '',
    login: props.user?.login || '',
    password: '',
    role: props.user?.role || 'methodist',
    is_blocked: props.user?.is_blocked || false,
});

const saving = ref(false);
const error = ref('');

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        const data = { ...form };
        if (isEdit && !data.password) {
            delete data.password;
        }

        if (isEdit) {
            await usersApi.update(props.user.id, data);
        } else {
            await usersApi.create(data);
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
/* Те же стили, что и в других формах */
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
    max-width: 500px;
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
    background: transparent; border: none; font-size: 20px;
    cursor: pointer; color: #64748b;
}

.modal-body { padding: 24px; }
.field { margin-bottom: 16px; }
.field label {
    display: block; font-size: 13px; color: #475569; margin-bottom: 6px;
}
.field input, .field select {
    width: 100%; padding: 10px; border: 1px solid #cbd5e1;
    border-radius: 6px; font-size: 14px; box-sizing: border-box;
}
.field.checkbox label {
    display: flex; align-items: center; gap: 8px; cursor: pointer;
}
.field.checkbox input { width: auto; }

.error-box {
    background: #fee2e2; color: #991b1b; padding: 10px;
    border-radius: 6px; font-size: 13px; margin-bottom: 16px;
    white-space: pre-line;
}
.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 16px; border-top: 1px solid #e2e8f0; margin-top: 16px;
}
.btn {
    padding: 10px 20px; border: none; border-radius: 6px;
    cursor: pointer; font-size: 14px;
}
.btn-primary { background: #3b82f6; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #2563eb; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #e2e8f0; color: #1e293b; }
</style>