<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <div class="login-brand">
          <img src="/logo.svg" alt="Antricu" class="logo-img" />
          <div class="brand-text">
            <h1>Antricu</h1>
            <p class="login-subtitle">SISTEM MANAJEMEN ANTRIAN</p>
          </div>
        </div>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="Masukkan email" required />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" placeholder="Masukkan password" required />
        </div>

        <div v-if="error" class="error-alert">
          <AlertCircle :size="16" />
          <span>{{ error }}</span>
        </div>

        <button type="submit" class="btn btn-primary login-btn" :disabled="loading">
          <Loader2 v-if="loading" :size="18" class="spin" />
          {{ loading ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <div class="login-divider"><span>atau</span></div>

      <div class="login-links">
        <router-link to="/kiosk/print" class="link-card">
          <Printer :size="20" />
          <span>Kiosk Cetak Antrian</span>
        </router-link>
        <router-link to="/kiosk/display" class="link-card">
          <Monitor :size="20" />
          <span>Display Ruang Tunggu</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { AlertCircle, Loader2, Printer, Monitor } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const form = ref({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await authStore.login(form.value.email, form.value.password)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal terhubung ke server'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
}

.login-card {
  background: var(--bg-white);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 40px 36px;
  width: 100%;
  max-width: 400px;
  box-shadow: var(--shadow-lg);
}

.login-header {
  margin-bottom: 40px;
}
.login-brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}
.brand-text {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
}
.logo-img {
  width: 100px;
  height: 100px;
  object-fit: contain;
}
.brand-text h1 {
  font-size: 2.75rem;
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.03em;
  line-height: 1;
  margin-bottom: 4px;
}
.login-subtitle {
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
}


.login-btn {
  width: 100%;
  padding: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  margin-top: 4px;
}
.login-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.error-alert {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: var(--danger-subtle);
  color: var(--danger);
  border-radius: var(--radius-sm);
  font-size: 0.82rem;
  font-weight: 500;
  margin-bottom: 12px;
}

.login-divider {
  text-align: center;
  margin: 24px 0;
  position: relative;
}
.login-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  border-top: 1px solid var(--border);
}
.login-divider span {
  background: var(--bg-white);
  padding: 0 12px;
  position: relative;
  color: var(--text-light);
  font-size: 0.8rem;
}

.login-links {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.link-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--text-secondary);
  transition: var(--transition);
}
.link-card:hover {
  border-color: var(--primary);
  color: var(--primary);
  background: var(--primary-subtle);
}

.spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
