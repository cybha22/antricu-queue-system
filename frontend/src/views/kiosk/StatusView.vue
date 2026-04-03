<template>
  <div class="status-page">
    <div class="status-card">
      <div class="status-header">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
          <rect width="40" height="40" rx="11" fill="#4f46e5"/>
          <path d="M12 15h16M12 20h11M12 25h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/>
        </svg>
        <h1>Status Antrian</h1>
      </div>

      <div v-if="loading" class="loading-state">
        <Loader2 :size="32" class="spin" color="var(--primary)" />
        <p>Memuat data antrian...</p>
      </div>

      <div v-else-if="queue" class="queue-detail">
        <div class="queue-number-display">{{ queue.number }}</div>
        <p class="queue-service-name">{{ queue.service?.name }}</p>

        <div class="status-badge-wrapper">
          <span class="status-badge" :class="'status-' + queue.status">
            <component :is="statusIcon" :size="16" />
            {{ statusText }}
          </span>
        </div>

        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Loket</span>
            <span class="detail-value">{{ queue.counter?.name || 'Belum dipanggil' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Waktu Dipanggil</span>
            <span class="detail-value">{{ queue.called_at ? new Date(queue.called_at).toLocaleTimeString('id') : '-' }}</span>
          </div>
        </div>

        <div v-if="queue.status === 'called' || queue.status === 'serving'" class="called-alert">
          <Volume2 v-if="queue.status === 'called'" :size="20" />
          <PlayCircle v-else :size="20" />
          <div>
            <strong v-if="queue.status === 'called'">Antrian Anda sedang dipanggil!</strong>
            <strong v-else>Anda sedang dilayani</strong>
            <p>Silakan menuju {{ queue.counter?.name }}</p>
          </div>
        </div>
      </div>

      <div v-else class="not-found">
        <SearchX :size="48" color="var(--text-light)" />
        <h3>Antrian Tidak Ditemukan</h3>
        <p>Nomor antrian tidak valid atau sudah kedaluwarsa</p>
      </div>

      <router-link to="/kiosk/print" class="btn btn-outline" style="width:100%;margin-top:20px;justify-content:center">
        Ambil Antrian Baru
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'
import { Loader2, Clock, Volume2, CheckCircle2, XCircle, SearchX, PlayCircle } from 'lucide-vue-next'

const route = useRoute()
const queue = ref(null)
const loading = ref(true)
let interval

const statusText = computed(() => {
  const m = { waiting: 'Menunggu', called: 'Dipanggil', serving: 'Sedang Dilayani', served: 'Selesai Dilayani', cancelled: 'Dibatalkan' }
  return m[queue.value?.status] || '-'
})

const statusIcon = computed(() => {
  const m = { waiting: Clock, called: Volume2, serving: PlayCircle, served: CheckCircle2, cancelled: XCircle }
  return m[queue.value?.status] || Clock
})

async function fetchStatus() {
  try {
    const res = await api.get(`/api/v1/kiosk/queue/${route.params.number}/status`)
    queue.value = res.data
  } catch (e) {
    queue.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStatus()
  interval = setInterval(fetchStatus, 5000)
})
onUnmounted(() => clearInterval(interval))
</script>

<style scoped>
.status-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  padding: 20px;
}
.status-card {
  background: var(--bg-white);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 40px 32px;
  width: 100%;
  max-width: 420px;
  box-shadow: var(--shadow-xl);
}
.status-header {
  text-align: center;
  margin-bottom: 28px;
}
.status-header h1 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-top: 12px;
  color: var(--text);
}
.loading-state {
  text-align: center;
  padding: 40px 0;
}
.loading-state p {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin-top: 12px;
}
.queue-detail { text-align: center; }
.queue-number-display {
  font-size: 4rem;
  font-weight: 800;
  color: var(--primary);
  line-height: 1;
  letter-spacing: -2px;
}
.queue-service-name {
  font-weight: 500;
  color: var(--text-secondary);
  margin-top: 6px;
  margin-bottom: 16px;
}
.status-badge-wrapper { margin-bottom: 20px; }
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 16px;
  border-radius: var(--radius-full);
  font-size: 0.85rem;
  font-weight: 600;
}
.status-waiting { background: var(--warning-subtle); color: #b45309; }
.status-called { background: var(--info-subtle); color: var(--info); }
.status-serving { background: var(--success-subtle); color: #15803d; }
.status-served { background: var(--success-subtle); color: var(--success); }
.status-cancelled { background: var(--danger-subtle); color: var(--danger); }

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  padding: 16px;
  background: var(--bg);
  border-radius: var(--radius-sm);
  margin-bottom: 16px;
}
.detail-item { text-align: center; }
.detail-label {
  font-size: 0.72rem;
  color: var(--text-light);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.detail-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text);
  display: block;
  margin-top: 2px;
}
.called-alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: var(--info-subtle);
  border: 1px solid rgba(59,130,246,0.2);
  border-radius: var(--radius-sm);
  color: var(--info);
  text-align: left;
}
.called-alert strong { font-size: 0.875rem; }
.called-alert p { font-size: 0.78rem; margin-top: 2px; opacity: 0.8; }
.not-found {
  text-align: center;
  padding: 32px 0;
}
.not-found h3 { font-size: 1.1rem; font-weight: 600; margin-top: 12px; color: var(--text); }
.not-found p { font-size: 0.82rem; color: var(--text-secondary); margin-top: 4px; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
