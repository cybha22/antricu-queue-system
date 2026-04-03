<template>
  <div>
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Menejemen Pengguna</h2>
        <div style="display:flex;gap:10px;align-items:center">
          <div class="search-box">
            <Search :size="16" class="search-icon" />
            <input v-model="search" placeholder="Cari pengguna..." @input="fetchData" />
          </div>
          <button class="btn btn-primary" @click="openModal()"><Plus :size="16" /> Tambah</button>
        </div>
      </div>
      <table>
        <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Peran</th><th>Tugas Loket</th><th>Aksi</th></tr></thead>
        <tbody>
          <tr v-for="(u, i) in items" :key="u.id">
            <td>{{ (page - 1) * 10 + i + 1 }}</td>
            <td style="font-weight:500">{{ u.name }}</td>
            <td>{{ u.email }}</td>
            <td><span class="badge" :class="u.role==='admin' ? 'badge-info' : 'badge-warning'">{{ u.role }}</span></td>
            <td>{{ u.counter?.name || 'Semua' }}</td>
            <td>
              <div style="display:flex;gap:6px">
                <button class="btn btn-outline btn-sm" @click="openModal(u)"><Pencil :size="14" /> Edit</button>
                <button class="btn btn-danger btn-sm" @click="remove(u.id)"><Trash2 :size="14" /></button>
              </div>
            </td>
          </tr>
          <tr v-if="!items.length"><td colspan="6" style="text-align:center;color:var(--text-light);padding:40px">Belum ada data</td></tr>
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
        <h3 class="modal-title">{{ editing ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h3>
        <form @submit.prevent="save">
          <div class="form-group"><label>Nama</label><input v-model="form.name" required /></div>
          <div class="form-group"><label>Email</label><input v-model="form.email" type="email" required /></div>
          <div class="form-group"><label>Password {{ editing ? '(kosongkan jika tidak diubah)' : '' }}</label><input v-model="form.password" type="password" :required="!editing" /></div>
          <div class="form-group">
            <label>Peran</label>
            <select v-model="form.role" required>
              <option value="admin">Admin</option>
              <option value="operator">Operator</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tugas Loket</label>
            <select v-model="form.counter_id">
              <option :value="null">Semua</option>
              <option v-for="c in counters" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
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
const counters = ref([])
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', email: '', password: '', role: 'operator', counter_id: null })

async function fetchData() {
  const res = await api.get('/api/v1/admin/users', { params: { page: page.value, search: search.value } })
  items.value = res.data.data
  lastPage.value = res.data.last_page
}
async function fetchCounters() {
  const res = await api.get('/api/v1/admin/counters', { params: { per_page: 100 } })
  counters.value = res.data.data
}
function openModal(item = null) {
  editing.value = item
  form.value = item ? { ...item, password: '' } : { name: '', email: '', password: '', role: 'operator', counter_id: null }
  showModal.value = true
}
async function save() {
  const data = { ...form.value }
  if (editing.value && !data.password) delete data.password
  if (editing.value) await api.put(`/api/v1/admin/users/${editing.value.id}`, data)
  else await api.post('/api/v1/admin/users', data)
  showModal.value = false
  await fetchData()
}
async function remove(id) {
  if (!confirm('Hapus pengguna ini?')) return
  await api.delete(`/api/v1/admin/users/${id}`)
  await fetchData()
}
onMounted(() => { fetchData(); fetchCounters() })
</script>

<style scoped>
.search-box { position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
</style>
