<template>
  <div>
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Menejemen Loket</h2>
        <div style="display:flex;gap:10px;align-items:center">
          <div class="search-box">
            <Search :size="16" class="search-icon" />
            <input v-model="search" placeholder="Cari loket..." @input="fetchData" />
          </div>
          <button class="btn btn-primary" @click="openModal()"><Plus :size="16" /> Tambah</button>
        </div>
      </div>
      <table>
        <thead><tr><th>No</th><th>Nama Loket</th><th>Layanan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <tr v-for="(c, i) in items" :key="c.id">
            <td>{{ (page - 1) * 10 + i + 1 }}</td>
            <td style="font-weight:500">{{ c.name }}</td>
            <td>{{ c.service?.name || '-' }}</td>
            <td><span class="badge" :class="c.is_active ? 'badge-success' : 'badge-danger'">{{ c.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
              <div style="display:flex;gap:6px">
                <button class="btn btn-outline btn-sm" @click="openModal(c)"><Pencil :size="14" /> Edit</button>
                <button class="btn btn-danger btn-sm" @click="remove(c.id)"><Trash2 :size="14" /></button>
              </div>
            </td>
          </tr>
          <tr v-if="!items.length"><td colspan="5" style="text-align:center;color:var(--text-light);padding:40px">Belum ada data</td></tr>
        </tbody>
      </table>
      <div class="pagination" v-if="lastPage > 1">
        <button :disabled="page<=1" @click="page--;fetchData()"><ChevronLeft :size="16" /></button>
        <button v-for="p in lastPage" :key="p" :class="{active:p===page}" @click="page=p;fetchData()">{{ p }}</button>
        <button :disabled="page>=lastPage" @click="page++;fetchData()"><ChevronRight :size="16" /></button>
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal-content">
        <h3 class="modal-title">{{ editing ? 'Edit Loket' : 'Tambah Loket' }}</h3>
        <form @submit.prevent="save">
          <div class="form-group"><label>Nama Loket</label><input v-model="form.name" required /></div>
          <div class="form-group">
            <label>Layanan</label>
            <select v-model="form.service_id" required>
              <option value="">Pilih Layanan</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="form-group"><label>Status Aktif</label><div class="toggle-switch" :class="{active:form.is_active}" @click="form.is_active=!form.is_active"></div></div>
          <div class="form-actions">
            <button type="button" class="btn btn-outline" @click="showModal=false">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { Search, Plus, Pencil, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const items = ref([])
const services = ref([])
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', service_id: '', is_active: true })

async function fetchData() {
  const res = await api.get('/api/v1/admin/counters', { params: { page: page.value, search: search.value } })
  items.value = res.data.data
  lastPage.value = res.data.last_page
}
async function fetchServices() {
  const res = await api.get('/api/v1/admin/services', { params: { per_page: 100 } })
  services.value = res.data.data
}
function openModal(item = null) {
  editing.value = item
  form.value = item ? { ...item } : { name: '', service_id: '', is_active: true }
  showModal.value = true
}
async function save() {
  if (editing.value) await api.put(`/api/v1/admin/counters/${editing.value.id}`, form.value)
  else await api.post('/api/v1/admin/counters', form.value)
  showModal.value = false
  await fetchData()
}
async function remove(id) {
  if (!confirm('Hapus loket ini?')) return
  await api.delete(`/api/v1/admin/counters/${id}`)
  await fetchData()
}
onMounted(() => { fetchData(); fetchServices() })
</script>

<style scoped>
.search-box { position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
</style>
