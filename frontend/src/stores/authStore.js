import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const loading = ref(false)

    async function login(email, password) {
        await api.get('/sanctum/csrf-cookie')
        const res = await api.post('/api/v1/auth/login', { email, password })
        user.value = res.data.user
        return res.data
    }

    async function logout() {
        await api.post('/api/v1/auth/logout')
        user.value = null
    }

    async function fetchUser() {
        try {
            const res = await api.get('/api/v1/auth/user')
            user.value = res.data
        } catch {
            user.value = null
        }
    }

    return { user, loading, login, logout, fetchUser }
})
