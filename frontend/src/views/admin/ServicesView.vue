<template>
  <div>
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Menejemen Layanan</h2>
        <div style="display:flex;gap:10px;align-items:center">
          <div class="search-box">
            <Search :size="16" class="search-icon" />
            <input v-model="search" placeholder="Cari layanan..." @input="fetchData" />
          </div>
          <button class="btn btn-primary" @click="openModal()"><Plus :size="16" /> Tambah</button>
        </div>
      </div>
      <table>
        <thead><tr><th>No</th><th>Nama</th><th>Prefix</th><th>Digit</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <tr v-for="(s, i) in items" :key="s.id">
            <td>{{ (page - 1) * 10 + i + 1 }}</td>
            <td style="font-weight:500">{{ s.name }}</td>
            <td><span class="badge badge-info">{{ s.prefix }}</span></td>
            <td>{{ s.total_digits }}</td>
            <td><span class="badge" :class="s.is_active ? 'badge-success' : 'badge-danger'">{{ s.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
              <div style="display:flex;gap:6px">
                <button class="btn btn-outline btn-sm" @click="openModal(s)"><Pencil :size="14" /> Edit</button>
                <button class="btn btn-danger btn-sm" @click="remove(s.id)"><Trash2 :size="14" /></button>
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
        <h3 class="modal-title">{{ editing ? 'Edit Layanan' : 'Tambah Layanan' }}</h3>
        <form @submit.prevent="save">
          <div class="form-group"><label>Nama</label><input v-model="form.name" required /></div>
          <div class="form-group"><label>Prefix</label><input v-model="form.prefix" maxlength="10" required /></div>
          <div class="form-group"><label>Jumlah Digit</label><input v-model.number="form.total_digits" type="number" min="1" max="5" required /></div>
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
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = ref({ name: '', prefix: '', total_digits: 3, is_active: true })

async function fetchData() {
  const res = await api.get('/api/v1/admin/services', { params: { page: page.value, search: search.value } })
  items.value = res.data.data
  lastPage.value = res.data.last_page
}
function openModal(item = null) {
  editing.value = item
  form.value = item ? { ...item } : { name: '', prefix: '', total_digits: 3, is_active: true }
  showModal.value = true
}
async function save() {
  if (editing.value) await api.put(`/api/v1/admin/services/${editing.value.id}`, form.value)
  else await api.post('/api/v1/admin/services', form.value)
  showModal.value = false
  await fetchData()
}
async function remove(id) {
  if (!confirm('Hapus layanan ini?')) return
  await api.delete(`/api/v1/admin/services/${id}`)
  await fetchData()
}
onMounted(fetchData)
</script>

<style scoped>
.search-box { position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
</style>
