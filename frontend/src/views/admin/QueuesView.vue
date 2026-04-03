<template>
  <div>
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Daftar Antrian</h2>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div class="search-box">
            <Search :size="16" class="search-icon" />
            <input v-model="search" placeholder="Cari nomor..." @input="fetchData" />
          </div>
          <select v-model="filterStatus" @change="fetchData" style="max-width:150px">
            <option value="">Semua Status</option>
            <option value="waiting">Menunggu</option>
            <option value="called">Dipanggil</option>
            <option value="serving">Sedang Dilayani</option>
            <option value="served">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
          <input type="date" v-model="filterDate" @change="fetchData" style="max-width:170px" />
        </div>
      </div>
      <table>
        <thead><tr><th>No</th><th>Nomor</th><th>Layanan</th><th>Loket</th><th>Status</th><th>Dipanggil</th><th>Dilayani</th><th>Selesai</th><th>Aksi</th></tr></thead>
        <tbody>
          <tr v-for="(q, i) in items" :key="q.id">
            <td>{{ (page - 1) * 10 + i + 1 }}</td>
            <td><strong>{{ q.number }}</strong></td>
            <td>{{ q.service?.name || '-' }}</td>
            <td>{{ q.counter?.name || '-' }}</td>
            <td><span class="badge" :class="statusClass(q.status)">{{ statusLabel(q.status) }}</span></td>
            <td>{{ q.called_at ? new Date(q.called_at).toLocaleTimeString('id') : '-' }}</td>
            <td>{{ q.serving_at ? new Date(q.serving_at).toLocaleTimeString('id') : '-' }}</td>
            <td>{{ q.served_at ? new Date(q.served_at).toLocaleTimeString('id') : '-' }}</td>
            <td><button class="btn btn-danger btn-sm" @click="remove(q.id)"><Trash2 :size="14" /></button></td>
          </tr>
          <tr v-if="!items.length"><td colspan="9" style="text-align:center;color:var(--text-light);padding:40px">Belum ada data</td></tr>
        </tbody>
      </table>
      <div class="pagination" v-if="lastPage > 1">
        <button :disabled="page<=1" @click="page--;fetchData()"><ChevronLeft :size="16" /></button>
        <button v-for="p in lastPage" :key="p" :class="{active:p===page}" @click="page=p;fetchData()">{{ p }}</button>
        <button :disabled="page>=lastPage" @click="page++;fetchData()"><ChevronRight :size="16" /></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { Search, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const items = ref([])
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const filterStatus = ref('')
const filterDate = ref('')

async function fetchData() {
  const params = { page: page.value, search: search.value }
  if (filterStatus.value) params.status = filterStatus.value
  if (filterDate.value) params.date = filterDate.value
  const res = await api.get('/api/v1/admin/queues', { params })
  items.value = res.data.data
  lastPage.value = res.data.last_page
}
function statusClass(s) { return { waiting:'badge-warning', called:'badge-info', serving:'badge-success', served:'badge-success', cancelled:'badge-danger' }[s] }
function statusLabel(s) { return { waiting:'Menunggu', called:'Dipanggil', serving:'Sedang Dilayani', served:'Selesai', cancelled:'Dibatalkan' }[s] }
async function remove(id) {
  if (!confirm('Hapus antrian ini?')) return
  await api.delete(`/api/v1/admin/queues/${id}`)
  await fetchData()
}
onMounted(fetchData)
</script>

<style scoped>
.search-box { position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
</style>
