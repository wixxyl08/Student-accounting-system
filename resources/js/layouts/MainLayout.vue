<template>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">Учёт обучающихся</div>
            <nav class="menu">
                <router-link to="/" class="menu-item">📊 Дашборд</router-link>
                <router-link to="/organizations" class="menu-item">🏢 Организации</router-link>
                <router-link to="/employees" class="menu-item">👥 Сотрудники</router-link>
                <router-link to="/programs" class="menu-item">📚 Программы</router-link>
                <router-link to="/groups" class="menu-item">🎓 Группы</router-link>
                <router-link to="/notifications" class="menu-item">🔔 Уведомления</router-link>
                <router-link to="/contracts" class="menu-item">📄 Договоры</router-link>
                <router-link v-if="auth.isAdmin" to="/admin/users" class="menu-item">👤 Пользователи</router-link>
<router-link v-if="auth.isAdmin" to="/admin/activity-log" class="menu-item">📋 Журнал</router-link>
            </nav>
            <div class="user-info">
                <div class="user-name">{{ auth.user?.full_name }}</div>
                <div class="user-role">{{ roleName }}</div>
                <button class="logout-btn" @click="handleLogout">Выйти</button>
            </div>
        </aside>
        <main class="content">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const roleName = computed(() => {
    return auth.user?.role === 'admin' ? 'Администратор' : 'Методист';
});

const handleLogout = async () => {
    await auth.logout();
    router.push('/login');
};
</script>

<style scoped>
.layout {
    display: flex;
    min-height: 100vh;
    font-family: Arial, sans-serif;
}

.sidebar {
    width: 260px;
    background: #1e293b;
    color: #fff;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.logo {
    font-size: 18px;
    font-weight: bold;
    padding-bottom: 20px;
    border-bottom: 1px solid #334155;
    margin-bottom: 20px;
}

.menu {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-grow: 1;
}

.menu-item {
    color: #cbd5e1;
    text-decoration: none;
    padding: 10px 12px;
    border-radius: 6px;
    transition: background 0.2s;
}

.menu-item:hover {
    background: #334155;
    color: #fff;
}

.menu-item.router-link-active {
    background: #3b82f6;
    color: #fff;
}

.user-info {
    border-top: 1px solid #334155;
    padding-top: 15px;
}

.user-name {
    font-weight: bold;
    margin-bottom: 4px;
}

.user-role {
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 12px;
}

.logout-btn {
    width: 100%;
    padding: 8px;
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.logout-btn:hover {
    background: #b91c1c;
}

.content {
    flex-grow: 1;
    padding: 30px;
    background: #f8fafc;
}
</style>