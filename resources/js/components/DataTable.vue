<template>
    <div class="data-table">
        <div class="table-toolbar">
            <input
                v-model="searchInput"
                type="text"
                class="search-input"
                placeholder="Поиск..."
                @input="handleSearch"
            />
            <button class="btn btn-primary" @click="$emit('create')">
                + Добавить
            </button>
        </div>

        <div v-if="loading" class="loading">Загрузка...</div>

        <table v-else class="table">
            <thead>
                <tr>
                    <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="rows.length === 0">
                    <td :colspan="columns.length + 1" class="empty">
                        Нет данных
                    </td>
                </tr>
                <tr v-for="row in rows" :key="row.id">
                    <td v-for="col in columns" :key="col.key">
                        {{ row[col.key] ?? '—' }}
                    </td>
                        <td class="actions">
                            <slot name="row-actions" :row="row"></slot>
                            <button class="btn-icon" @click="$emit('edit', row)" title="Редактировать">✏️</button>
                            <button class="btn-icon" @click="$emit('delete', row)" title="Удалить">🗑️</button>
                        </td>
                </tr>
            </tbody>    
        </table>

        <div v-if="meta && meta.last_page > 1" class="pagination">
            <button :disabled="meta.current_page === 1" @click="$emit('page', meta.current_page - 1)">←</button>
            <span>Стр. {{ meta.current_page }} из {{ meta.last_page }} (всего: {{ meta.total }})</span>
            <button :disabled="meta.current_page === meta.last_page" @click="$emit('page', meta.current_page + 1)">→</button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    meta: { type: Object, default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['search', 'create', 'edit', 'delete', 'page']);

const searchInput = ref('');

let searchTimer = null;
const handleSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        emit('search', searchInput.value);
    }, 400);
};
</script>

<style scoped>
.data-table {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    padding: 20px;
}

.table-toolbar {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.search-input {
    flex-grow: 1;
    max-width: 400px;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.btn {
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.table th {
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
    text-transform: uppercase;
}

.table tr:hover {
    background: #f8fafc;
}

.empty {
    text-align: center;
    color: #94a3b8;
    padding: 40px;
}

.actions {
    display: flex;
    gap: 6px;
}

.btn-icon {
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 14px;
}

.btn-icon:hover {
    background: #f1f5f9;
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