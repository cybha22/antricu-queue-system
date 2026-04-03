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
        <div class="stat-icon stat-icon-served"><CheckCircle2 :size="22" /></div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.served }}</div>
          <div class="stat-label">Dilayani</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-icon-time"><Timer :size="22" /></div>
        <div class="stat-info">
          <div class="stat-value">{{ avgWait }}<span class="stat-unit">mnt</span></div>
          <div class="stat-label">Rata-rata Tunggu</div>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">
      <div class="card chart-card">
        <div class="card-header-row">
          <h3>Tren Antrian 7 Hari Terakhir</h3>
        </div>
        <div class="chart-container">
          <div class="chart-bars">
            <div v-for="d in weeklyTrend" :key="d.date" class="chart-bar-group">
              <div class="chart-bar-wrapper">
                <div class="chart-bar bar-total" :style="{ height: barHeight(d.total) + 'px' }" :title="'Total: ' + d.total"></div>
                <div class="chart-bar bar-served" :style="{ height: barHeight(d.served) + 'px' }" :title="'Dilayani: ' + d.served"></div>
              </div>
              <span class="chart-label">{{ d.label }}</span>
            </div>
          </div>
          <div class="chart-legend">
            <span class="legend-item"><span class="legend-dot dot-total"></span> Total</span>
            <span class="legend-item"><span class="legend-dot dot-served"></span> Dilayani</span>
          </div>
        </div>
      </div>

      <div class="card service-card">
        <div class="card-header-row">
          <h3>Per Layanan Hari Ini</h3>
        </div>
        <div class="service-list">
          <div v-for="s in serviceStats" :key="s.id" class="service-row">
            <div class="service-info">
              <span class="service-prefix-badge">{{ s.prefix }}</span>
              <span class="service-name">{{ s.name }}</span>
            </div>
            <div class="service-numbers">
              <span class="service-num waiting">{{ s.waiting_today }}</span>
              <span class="service-num served">{{ s.served_today }}</span>
              <span class="service-num total">{{ s.total_today }}</span>
            </div>
          </div>
          <div class="service-row service-header">
            <div class="service-info"><span></span><span></span></div>
            <div class="service-numbers">
              <span class="service-label">Tunggu</span>
              <span class="service-label">Selesai</span>
              <span class="service-label">Total</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card counter-status-card">
      <div class="card-header-row">
        <h3>Status Loket</h3>
        <span class="badge badge-info">{{ activeCounters }}/{{ totalCounters }} aktif</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { ClipboardList, Clock, CheckCircle2, Timer } from 'lucide-vue-next'

const stats = ref({ total: 0, waiting: 0, called: 0, served: 0, cancelled: 0 })
const avgWait = ref(0)
const serviceStats = ref([])
const activeCounters = ref(0)
const totalCounters = ref(0)
const weeklyTrend = ref([])

function barHeight(value) {
  const max = Math.max(...weeklyTrend.value.map(d => d.total), 1)
  return Math.max((value / max) * 160, 4)
}

async function fetchDashboard() {
  try {
    const res = await api.get('/api/v1/admin/dashboard')
    stats.value = res.data.today_stats
    avgWait.value = res.data.avg_wait_time
    serviceStats.value = res.data.service_stats
    activeCounters.value = res.data.active_counters
    totalCounters.value = res.data.total_counters
    weeklyTrend.value = res.data.weekly_trend
  } catch (e) { /* ignore */ }
}

onMounted(fetchDashboard)
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
.stat-icon-served { background: var(--success-subtle); color: var(--success); }
.stat-icon-time { background: var(--info-subtle); color: var(--info); }
.stat-info { display: flex; flex-direction: column; }
.stat-unit { font-size: 0.7rem; color: var(--text-secondary); margin-left: 2px; }

.dashboard-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}
.card-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.card-header-row h3 {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text);
}

.chart-container { padding: 0 4px; }
.chart-bars {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  height: 180px;
  gap: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border);
}
.chart-bar-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.chart-bar-wrapper {
  display: flex;
  gap: 4px;
  align-items: flex-end;
}
.chart-bar {
  width: 18px;
  border-radius: 4px 4px 0 0;
  transition: height 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.bar-total { background: var(--primary); opacity: 0.3; }
.bar-served { background: var(--success); }
.chart-label {
  font-size: 0.72rem;
  color: var(--text-secondary);
  font-weight: 500;
}
.chart-legend {
  display: flex;
  gap: 16px;
  margin-top: 12px;
  justify-content: center;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: var(--text-secondary);
}
.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 3px;
}
.dot-total { background: var(--primary); opacity: 0.3; }
.dot-served { background: var(--success); }

.service-list { display: flex; flex-direction: column-reverse; }
.service-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid var(--border);
}
.service-row:first-child { border-bottom: none; }
.service-header { border-bottom: none; padding-bottom: 0; }
.service-info { display: flex; align-items: center; gap: 10px; }
.service-prefix-badge {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
  flex-shrink: 0;
}
.service-name { font-size: 0.85rem; font-weight: 500; color: var(--text); }
.service-numbers { display: flex; gap: 16px; }
.service-num {
  width: 40px;
  text-align: center;
  font-weight: 700;
  font-size: 0.9rem;
}
.service-num.waiting { color: #b45309; }
.service-num.served { color: var(--success); }
.service-num.total { color: var(--primary); }
.service-label {
  width: 40px;
  text-align: center;
  font-size: 0.68rem;
  color: var(--text-light);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}


</style>
