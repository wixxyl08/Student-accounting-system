<template>
    <div class="login-page">
        <div class="login-box">
            <h1>Вход в систему</h1>
            <p class="subtitle">Система учёта обучающихся</p>

            <form @submit.prevent="handleLogin">
                <div class="field">
                    <label>Логин</label>
                    <input v-model="form.login" type="text" required />
                </div>

                <div class="field">
                    <label>Пароль</label>
                    <input v-model="form.password" type="password" required />
                </div>

                <div v-if="error" class="error">{{ error }}</div>

                <button type="submit" :disabled="loading">
                    {{ loading ? 'Вход...' : 'Войти' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = reactive({
    login: '',
    password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    try {
        await auth.login(form.login, form.password);
        router.push('/');
    } catch (e) {
        error.value = e.response?.data?.message || 'Ошибка входа';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: #f1f5f9;
    font-family: Arial, sans-serif;
}

.login-box {
    background: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    width: 360px;
}

h1 {
    font-size: 22px;
    margin-bottom: 6px;
    color: #1e293b;
}

.subtitle {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 24px;
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

.field input {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.field input:focus {
    outline: none;
    border-color: #3b82f6;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 16px;
}

button {
    width: 100%;
    padding: 12px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
}

button:hover:not(:disabled) {
    background: #2563eb;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>