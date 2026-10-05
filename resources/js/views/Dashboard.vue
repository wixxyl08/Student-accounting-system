<template>
    <MainLayout>
        <h1>Дашборд</h1>
        <p class="subtitle">Добро пожаловать в систему учёта обучающихся</p>

        <div class="cards">
            <div class="card">
                <div class="card-value">—</div>
                <div class="card-label">Организации</div>
            </div>
            <div class="card">
                <div class="card-value">—</div>
                <div class="card-label">Сотрудники</div>
            </div>
            <div class="card">
                <div class="card-value">—</div>
                <div class="card-label">Активные группы</div>
            </div>
            <div class="card">
                <div class="card-value">—</div>
                <div class="card-label">Всего обучено</div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '../layouts/MainLayout.vue';
</script>

<style scoped>
h1 {
    font-size: 26px;
    color: #1e293b;
    margin-bottom: 6px;
}

.subtitle {
    color: #64748b;
    margin-bottom: 30px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.card {
    background: #fff;
    padding: 24px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-value {
    font-size: 32px;
    font-weight: bold;
    color: #3b82f6;
    margin-bottom: 8px;
}

.card-label {
    color: #64748b;
    font-size: 14px;
}
</style>