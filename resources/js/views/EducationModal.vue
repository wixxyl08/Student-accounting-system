<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-header">
                <h2>Образование: {{ employee.full_name }}</h2>
                <button class="close-btn" @click="$emit('close')">✕</button>
            </div>

            <div class="modal-body">
                <button class="btn btn-primary add-btn" @click="openForm">
                    + Добавить образование
                </button>

                <div v-if="loading" class="loading">Загрузка...</div>

                <table v-else-if="educations.length" class="table">
                    <thead>
                        <tr>
                            <th>Уровень</th>
                            <th>Заведение</th>
                            <th>Год</th>
                            <th>Специальность</th>
                            <th>Файл</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="edu in educations" :key="edu.id">
                            <td>{{ edu.level_name }}</td>
                            <td>{{ edu.institution || '—' }}</td>
                            <td>{{ edu.graduation_year || '—' }}</td>
                            <td>{{ edu.specialty || '—' }}</td>
                            <td>
                                <button v-if="edu.has_file" class="link-btn" @click="downloadFile(edu)">
                                    📎 Скачать
                                </button>
                                <span v-else>—</span>
                            </td>
                            <td>
                                <button class="btn-icon" @click="editEducation(edu)">✏️</button>
                                <button class="btn-icon" @click="deleteEducation(edu)">🗑️</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="empty">Нет записей об образовании</div>
            </div>
        </div>

        <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
            <div class="modal small">
                <div class="modal-header">
                    <h2>{{ editingEducation ? 'Редактирование' : 'Новое образование' }}</h2>
                    <button class="close-btn" @click="closeForm">✕</button>
                </div>

                <form @submit.prevent="handleSubmit" class="modal-body">
                    <div class="field">
                        <label>Уровень образования *</label>
                        <select v-model="form.level" required>
                            <option value="higher">Высшее</option>
                            <option value="secondary_professional">Средне-профессиональное</option>
                            <option value="secondary_general">Среднее общее</option>
                            <option value="other">Иное</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Учебное заведение</label>
                        <input v-model="form.institution" type="text" />
                    </div>

                    <div class="row">
                        <div class="field">
                            <label>Год окончания</label>
                            <input v-model="form.graduation_year" type="number" min="1900" max="2030" />
                        </div>
                        <div class="field">
                            <label>Специальность</label>
                            <input v-model="form.specialty" type="text" />
                        </div>
                    </div>

                    <div class="field">
                        <label>Файл диплома (PDF, JPG, PNG, до 10 МБ)</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange" />
                        <div v-if="form.file_name" class="file-name">Прикреплён: {{ form.file_name }}</div>
                    </div>

                    <div v-if="error" class="error-box">{{ error }}</div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeForm">Отмена</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { educationsApi } from '../api/educations';
import api from '../api';

const props = defineProps({
    employee: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const educations = ref([]);
const loading = ref(false);

const showForm = ref(false);
const editingEducation = ref(null);
const form = reactive({
    level: 'higher',
    institution: '',
    graduation_year: '',
    specialty: '',
    file: null,
    file_name: '',
});

const saving = ref(false);
const error = ref('');

const loadData = async () => {
    loading.value = true;
    try {
        const { data } = await educationsApi.list({
            employee_id: props.employee.id,
            per_page: 100,
        });
        educations.value = data.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const openForm = () => {
    editingEducation.value = null;
    Object.assign(form, {
        level: 'higher',
        institution: '',
        graduation_year: '',
        specialty: '',
        file: null,
        file_name: '',
    });
    showForm.value = true;
};

const editEducation = (edu) => {
    editingEducation.value = edu;
    Object.assign(form, {
        level: edu.level,
        institution: edu.institution || '',
        graduation_year: edu.graduation_year || '',
        specialty: edu.specialty || '',
        file: null,
        file_name: '',
    });
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingEducation.value = null;
    error.value = '';
};

const onFileChange = (e) => {
    form.file = e.target.files[0] || null;
    form.file_name = form.file ? form.file.name : '';
};

const handleSubmit = async () => {
    saving.value = true;
    error.value = '';
    try {
        const fd = new FormData();
        fd.append('employee_id', props.employee.id);
        fd.append('level', form.level);
        if (form.institution) fd.append('institution', form.institution);
        if (form.graduation_year) fd.append('graduation_year', form.graduation_year);
        if (form.specialty) fd.append('specialty', form.specialty);
        if (form.file) fd.append('file', form.file);

        if (editingEducation.value) {
            await educationsApi.update(editingEducation.value.id, fd);
        } else {
            await educationsApi.create(fd);
        }

        closeForm();
        loadData();
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

const deleteEducation = async (edu) => {
    if (!confirm('Удалить запись об образовании?')) return;
    try {
        await educationsApi.remove(edu.id);
        loadData();
    } catch (e) {
        alert('Ошибка удаления');
    }
};
const downloadFile = async (edu) => {
    try {
        const response = await api.get(`/educations/${edu.id}/download`, {
            responseType: 'blob',
        });

        // Определяем имя файла из заголовка Content-Disposition
        const contentDisposition = response.headers['content-disposition'] || '';
        let filename = `diplom_${edu.id}.pdf`;
        
        const match = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        if (match && match[1]) {
            filename = match[1].replace(/['"]/g, '');
        } else if (edu.file_path) {
            // Или из пути файла
            const ext = edu.file_path.split('.').pop();
            filename = `diplom_${edu.id}.${ext}`;
        }

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (e) {
        alert('Ошибка скачивания файла');
    }
};
onMounted(loadData);
</script>

<style scoped>
/* Те же стили, что были раньше */
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
    max-width: 800px;
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

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.file-name {
    margin-top: 6px;
    font-size: 12px;
    color: #16a34a;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
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
.link-btn {
    background: transparent;
    border: none;
    color: #3b82f6;
    cursor: pointer;
    font-size: 14px;
    text-decoration: underline;
    padding: 0;
}

.link-btn:hover {
    color: #2563eb;
}

.btn-primary { background: #3b82f6; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #2563eb; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: #e2e8f0; color: #1e293b; }
</style>