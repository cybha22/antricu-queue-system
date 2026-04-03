<template>
  <div>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon stat-icon-total"><ClipboardList :size="22" /></div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.total }}</div>
          <div class="stat-label">Total Hari Ini</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-waiting"><Clock :size="22" /></div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.waiting }}</div>
          <div class="stat-label">Menunggu</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-called"><Volume2 :size="22" /></div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.called }}</div>
          <div class="stat-label">Dipanggil</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-served"><CheckCircle2 :size="22" /></div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.served }}</div>
          <div class="stat-label">Dilayani</div>
        </div>
      </div>
    </div>

    <div class="call-section">
      <div class="card counter-card">
        <div class="counter-header">
          <Building2 :size="20" color="var(--text-secondary)" />
          <span class="counter-label">Loket Anda</span>
        </div>
        <h2 class="counter-name">{{ counterData?.name || 'Tidak Ada Loket' }}</h2>
        <p class="counter-service">{{ counterData?.service?.name || 'Belum ditugaskan' }}</p>
        <span class="badge" :class="counterData?.is_active ? 'badge-success' : 'badge-danger'" style="margin-top:8px">
          {{ counterData?.is_active ? 'Sedang Buka' : 'Tutup' }}
        </span>

        <div class="counter-actions">
          <button v-if="!counterData?.is_active" class="btn btn-success btn-lg" @click="openCounter" style="width:100%">
            <DoorOpen :size="18" /> Buka Loket
          </button>
          <button v-else class="btn btn-danger btn-lg" @click="closeCounter" style="width:100%">
            <DoorClosed :size="18" /> Tutup Loket
          </button>
        </div>
      </div>

      <div class="card queue-card">
        <div class="queue-header">
          <span class="queue-label">Antrian Saat Ini</span>
          <span class="badge badge-warning">{{ waitingCount }} menunggu</span>
        </div>
        <div class="queue-number" v-if="currentQueue">{{ currentQueue.number }}</div>
        <div class="queue-number empty" v-else>—</div>
        <p class="queue-service-name" v-if="currentQueue">{{ currentQueue.service?.name }}</p>

        <div class="call-controls">
          <template v-if="!currentQueue">
            <button
              class="btn btn-primary btn-lg call-btn flex-1"
              @click="callNext(false)"
              :disabled="!counterData?.is_active || calling"
            >
              <Loader2 v-if="calling" :size="18" class="spin" />
              <Volume2 v-else :size="18" />
              {{ calling ? 'Memproses...' : 'Panggil Selanjutnya' }}
            </button>
          </template>
          
          <template v-else-if="currentQueue.status === 'called'">
            <button
              class="btn btn-warning btn-lg call-btn flex-1"
              style="color: #000;"
              @click="serveQueue"
              :disabled="calling"
            >
              <Loader2 v-if="calling" :size="18" class="spin" />
              <PlayCircle v-else :size="18" />
              Mulai Layanan
            </button>
            <button
              class="btn btn-outline btn-lg recall-btn"
              @click="callNext(true)"
              title="Panggil Ulang"
              :disabled="calling"
            >
              <Mic :size="18" class="text-primary" />
            </button>
            <button
              class="btn btn-danger btn-lg recall-btn"
              @click="skipQueue"
              title="Lewati / Batalkan"
              :disabled="calling"
            >
              <XCircle :size="18" />
            </button>
          </template>

          <template v-else-if="currentQueue.status === 'serving'">
            <button
              class="btn btn-success btn-lg call-btn flex-1"
              @click="completeQueue"
              :disabled="calling"
            >
              <Loader2 v-if="calling" :size="18" class="spin" />
              <CheckSquare v-else :size="18" />
              Selesai Dilayani
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../../services/api'
import {
  ClipboardList, Clock, Volume2, CheckCircle2,
  Building2, DoorOpen, DoorClosed, Loader2, Mic, PlayCircle, CheckSquare, XCircle
} from 'lucide-vue-next'

const counterData = ref(null)
const currentQueue = ref(null)
const waitingCount = ref(0)
const stats = ref({ total: 0, waiting: 0, called: 0, served: 0 })
const calling = ref(false)
let pollInterval

async function fetchData() {
  try {
    const res = await api.get('/api/v1/operator/queue-call')
    counterData.value = res.data.counter
    currentQueue.value = res.data.current_queue
    waitingCount.value = res.data.waiting_count
    stats.value = res.data.today_stats
  } catch (e) { /* ignore */ }
}

async function openCounter() {
  await api.post('/api/v1/operator/queue-call/open')
  await fetchData()
}

async function closeCounter() {
  await api.post('/api/v1/operator/queue-call/close')
  await fetchData()
}

async function callNext(isRecall = false) {
  calling.value = true
  try {
    const res = await api.post('/api/v1/operator/queue-call/next', { is_recall: isRecall })
    currentQueue.value = res.data.queue
    await fetchData()
  } finally {
    calling.value = false
  }
}

async function serveQueue() {
  calling.value = true
  try {
    await api.post('/api/v1/operator/queue-call/serve')
    await fetchData()
  } finally {
    calling.value = false
  }
}

async function completeQueue() {
  calling.value = true
  try {
    await api.post('/api/v1/operator/queue-call/complete')
    await fetchData()
  } finally {
    calling.value = false
  }
}

async function skipQueue() {
  if (!confirm('Yakin ingin melewati/membatalkan antrian ini?')) return
  calling.value = true
  try {
    await api.post('/api/v1/operator/queue-call/skip')
    await fetchData()
  } finally {
    calling.value = false
  }
}

onMounted(() => {
  fetchData()
  pollInterval = setInterval(fetchData, 5000)
})
onUnmounted(() => clearInterval(pollInterval))
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px 20px;
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon-total { background: var(--primary-subtle); color: var(--primary); }
.stat-icon-waiting { background: var(--warning-subtle); color: #b45309; }
.stat-icon-called { background: var(--info-subtle); color: var(--info); }
.stat-icon-served { background: var(--success-subtle); color: var(--success); }
.stat-info { display: flex; flex-direction: column; }

.call-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.counter-card {
  display: flex;
  flex-direction: column;
}
.counter-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}
.counter-label {
  font-size: 0.82rem;
  color: var(--text-secondary);
  font-weight: 500;
}
.counter-name {
  font-size: 1.4rem;
  font-weight: 700;
}
.counter-service {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin-top: 2px;
}
.counter-actions {
  margin-top: auto;
  padding-top: 20px;
}

.queue-card {
  text-align: center;
  display: flex;
  flex-direction: column;
}
.queue-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.queue-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
}
.queue-number {
  font-size: 4.5rem;
  font-weight: 800;
  color: var(--primary);
  line-height: 1;
  margin-bottom: 8px;
  letter-spacing: -2px;
}
.queue-number.empty { color: var(--text-light); }
.queue-service-name {
  font-weight: 500;
  color: var(--text-secondary);
  margin-bottom: 4px;
}
.call-controls {
  margin-top: auto;
  display: flex;
  gap: 12px;
  width: 100%;
}
.call-btn {
  padding: 14px;
  font-size: 0.95rem;
}
.flex-1 { flex: 1; }
.recall-btn {
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.recall-btn:hover {
  background: var(--primary-subtle);
  border-color: var(--primary);
}
.text-primary { color: var(--primary); }
.call-btn:disabled, .recall-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
