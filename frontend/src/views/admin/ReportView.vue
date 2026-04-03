<template>
  <div>
    <div class="filter-bar card">
      <div class="filter-row">
        <div class="filter-group">
          <label>Dari</label>
          <input type="date" v-model="dateFrom" class="form-input" />
        </div>
        <div class="filter-group">
          <label>Sampai</label>
          <input type="date" v-model="dateTo" class="form-input" />
        </div>
        <button class="btn btn-primary" @click="fetchReport">
          <Search :size="16" />
          Tampilkan
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <Loader2 :size="28" class="spin" color="var(--primary)" />
      <span>Memuat laporan...</span>
    </div>

    <template v-else-if="report">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon stat-icon-total"><ClipboardList :size="22" /></div>
          <div class="stat-info">
            <div class="stat-value">{{ report.summary.total }}</div>
            <div class="stat-label">Total Antrian</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-served"><CheckCircle2 :size="22" /></div>
          <div class="stat-info">
            <div class="stat-value">{{ report.summary.served }}</div>
            <div class="stat-label">Dilayani</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-cancelled"><XCircle :size="22" /></div>
          <div class="stat-info">
            <div class="stat-value">{{ report.summary.cancelled }}</div>
            <div class="stat-label">Dibatalkan</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-time"><Timer :size="22" /></div>
          <div class="stat-info">
            <div class="stat-value">{{ report.summary.avg_wait }}<span class="stat-unit">mnt</span></div>
            <div class="stat-label">Rata-rata Tunggu</div>
          </div>
        </div>
      </div>

      <div class="report-grid">
        <div class="card">
          <h3 class="card-title">Grafik Harian</h3>
          <div class="chart-bars">
            <div v-for="d in report.daily" :key="d.date" class="chart-bar-group">
              <div class="chart-bar-wrapper">
                <div class="chart-bar bar-total" :style="{ height: barHeight(d.total) + 'px' }"></div>
                <div class="chart-bar bar-served" :style="{ height: barHeight(d.served) + 'px' }"></div>
              </div>
              <span class="chart-label">{{ d.label }}</span>
            </div>
          </div>
          <div class="chart-legend">
            <span class="legend-item"><span class="legend-dot dot-total"></span> Total</span>
            <span class="legend-item"><span class="legend-dot dot-served"></span> Dilayani</span>
          </div>
        </div>

        <div class="card">
          <h3 class="card-title">Per Layanan</h3>
          <table class="table">
            <thead>
              <tr>
                <th>Layanan</th>
                <th style="text-align:center">Total</th>
                <th style="text-align:center">Selesai</th>
                <th style="text-align:center">Batal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in report.per_service" :key="s.id">
                <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span class="service-prefix-badge">{{ s.prefix }}</span>
                    {{ s.name }}
                  </div>
                </td>
                <td style="text-align:center;font-weight:700;color:var(--primary)">{{ s.total }}</td>
                <td style="text-align:center;font-weight:700;color:var(--success)">{{ s.served }}</td>
                <td style="text-align:center;font-weight:700;color:var(--danger)">{{ s.cancelled }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { ClipboardList, CheckCircle2, XCircle, Timer, Search, Loader2 } from 'lucide-vue-next'

const today = new Date().toISOString().split('T')[0]
const weekAgo = new Date(Date.now() - 6 * 86400000).toISOString().split('T')[0]
const dateFrom = ref(weekAgo)
const dateTo = ref(today)
const report = ref(null)
const loading = ref(false)

function barHeight(value) {
  if (!report.value) return 4
  const max = Math.max(...report.value.daily.map(d => d.total), 1)
  return Math.max((value / max) * 140, 4)
}

async function fetchReport() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/admin/reports', { params: { from: dateFrom.value, to: dateTo.value } })
    report.value = res.data
  } catch (e) { /* ignore */ }
  finally { loading.value = false }
}

onMounted(fetchReport)
</script>

<style scoped>
.filter-bar { margin-bottom: 20px; }
.filter-row {
  display: flex;
  align-items: flex-end;
  gap: 16px;
}
.filter-group { display: flex; flex-direction: column; gap: 4px; }
.filter-group label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.filter-group .form-input { width: 160px; }

.loading-state {
  text-align: center;
  padding: 60px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: var(--text-secondary);
}

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
.stat-icon-served { background: var(--success-subtle); color: var(--success); }
.stat-icon-cancelled { background: var(--danger-subtle); color: var(--danger); }
.stat-icon-time { background: var(--info-subtle); color: var(--info); }
.stat-info { display: flex; flex-direction: column; }
.stat-unit { font-size: 0.7rem; color: var(--text-secondary); margin-left: 2px; }

.report-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.card-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 16px;
}

.chart-bars {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  height: 160px;
  gap: 8px;
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
.chart-bar-wrapper { display: flex; gap: 3px; align-items: flex-end; }
.chart-bar {
  width: 14px;
  border-radius: 3px 3px 0 0;
  transition: height 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.bar-total { background: var(--primary); opacity: 0.3; }
.bar-served { background: var(--success); }
.chart-label {
  font-size: 0.65rem;
  color: var(--text-secondary);
  font-weight: 500;
}
.chart-legend {
  display: flex;
  gap: 16px;
  margin-top: 10px;
  justify-content: center;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.72rem;
  color: var(--text-secondary);
}
.legend-dot { width: 8px; height: 8px; border-radius: 2px; }
.dot-total { background: var(--primary); opacity: 0.3; }
.dot-served { background: var(--success); }

.service-prefix-badge {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.75rem;
  flex-shrink: 0;
}

.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
