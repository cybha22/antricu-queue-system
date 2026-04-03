<template>
  <div>
    <div class="card" style="max-width:640px">
      <div class="card-header">
        <h2 class="card-title">Pengaturan Instansi</h2>
      </div>
      <form @submit.prevent="save">
        <div class="form-group">
          <label>Logo</label>
          <input type="file" accept="image/*" @change="onFile" />
        </div>
        <div class="form-group"><label>Nama Instansi</label><input v-model="form.institution_name" required /></div>
        <div class="form-group"><label>Alamat</label><textarea v-model="form.address" rows="3"></textarea></div>
        <div class="form-group"><label>Nomor Telepon</label><input v-model="form.phone" /></div>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <Loader2 v-if="saving" :size="16" class="spin" />
          {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { Loader2 } from 'lucide-vue-next'

const form = ref({ institution_name: '', address: '', phone: '' })
const settingId = ref(null)
const logoFile = ref(null)
const saving = ref(false)

async function fetchData() {
  const res = await api.get('/api/v1/admin/settings')
  if (res.data.data?.length) {
    const s = res.data.data[0]
    settingId.value = s.id
    form.value = { institution_name: s.institution_name, address: s.address, phone: s.phone }
  }
}
function onFile(e) { logoFile.value = e.target.files[0] }
async function save() {
  saving.value = true
  try {
    const fd = new FormData()
    fd.append('institution_name', form.value.institution_name)
    fd.append('address', form.value.address || '')
    fd.append('phone', form.value.phone || '')
    if (logoFile.value) fd.append('logo', logoFile.value)
    fd.append('_method', 'PUT')
    await api.post(`/api/v1/admin/settings/${settingId.value}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    alert('Pengaturan berhasil disimpan')
  } finally { saving.value = false }
}
onMounted(fetchData)
</script>

<style scoped>
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
