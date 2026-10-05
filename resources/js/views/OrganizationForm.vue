<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>{{ isEdit ? 'Редактирование организации' : 'Новая организация' }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-body">
                <div class="field">
                    <label>Полное наименование *</label>
                    <input v-model="form.full_name" type="text" required />
                </div>

                <div class="row">
                    <div class="field">
                        <label>Краткое наименование</label>
                        <input v-model="form.short_name" type="text" />
                    </div>
                    <div class="field">
                        <label>ИНН</label>
                        <input v-model="form.inn" type="text" maxlength="12" />
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>КПП</label>
                        <input v-model="form.kpp" type="text" maxlength="9" />
                    </div>
                    <div class="field">
                        <label>ОГРН</label>
                        <input v-model="form.ogrn" type="text" maxlength="15" />
                    </div>
                </div>

                <div class="field">
                    <label>Юридический адрес</label>
                    <input v-model="form.legal_address" type="text" />
                </div>

                <div class="field">
                    <label>Фактический адрес</label>
                    <input v-model="form.actual_address" type="text" />
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
                        <label>Контактное лицо</label>
                        <input v-model="form.contact_person" type="text" />
                    </div>
                    <div class="field">
                        <label>Должность контактного лица</label>
                        <input v-model="form.contact_position" type="text" />
                    </div>
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
import { reactive, ref } from 'vue';
import { organizationsApi } from '../api/organizations';

const props = defineProps({
    organization: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEdit = !!props.organization;

const form = reactive({
    full_name: props.organization?.full_name || '',
    short_name: props.organization?.short_name || '',
    inn: props.organization?.inn || '',
    kpp: props.organization?.kpp || '',
    ogrn: props.organization?.ogrn || '',
    legal_address: props.organization?.legal_address || '',
    actual_address: props.organization?.actual_address || '',
    phone: props.organization?.phone || '',
    email: props.organization?.email || '',
    contact_person: props.organization?.contact_person || '',
    contact_position: props.organization?.contact_position || '',
    note: props.organization?.note || '',
});

const saving = ref(false);
const error = ref('');

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        if (isEdit) {
            await organizationsApi.update(props.organization.id, form);
        } else {
            await organizationsApi.create(form);
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
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
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

h2 {
    font-size: 20px;
    color: #1e293b;
}

.close-btn {
    background: transparent;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #64748b;
}

.modal-body {
    padding: 24px;
}

.field {
    margin-bottom: 16px;
}

.field label {
    display: block;
    font-size: 13px;
    color: #475569;
    margin-bottom: 6px;
}

.field input, .field textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
    font-family: inherit;
}

.field input:focus, .field textarea:focus {
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

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #e2e8f0;
    color: #1e293b;
}

.btn-secondary:hover {
    background: #cbd5e1;
}
</style>