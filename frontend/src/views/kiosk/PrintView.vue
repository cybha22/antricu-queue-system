<template>
  <div class="print-page">
    <div class="kiosk-card">
      <div class="kiosk-header">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
          <rect width="48" height="48" rx="14" fill="#4f46e5"/>
          <path d="M14 18h20M14 24h14M14 30h17" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
        <h1>Antricu</h1>
        <p>Ambil Nomor Antrian</p>
      </div>

      <div v-if="!ticket" class="service-list">
        <p class="service-hint">Pilih layanan yang Anda tuju</p>
        <button
          v-for="s in services"
          :key="s.id"
          class="service-btn"
          @click="takeQueue(s.id)"
          :disabled="taking"
        >
          <span class="service-prefix">{{ s.prefix }}</span>
          <span class="service-name">{{ s.name }}</span>
          <ChevronRight :size="18" color="var(--text-light)" />
        </button>
      </div>

      <div v-else class="ticket-view">
        <div class="ticket-badge">
          <CheckCircle2 :size="16" />
          <span>Antrian berhasil diambil</span>
        </div>

        <div class="ticket-struk" ref="strukRef">
          <p class="struk-institution">{{ settingName }}</p>
          <p class="struk-address">{{ settingAddress }}</p>
          <div class="struk-divider"></div>
          <p class="struk-label">Nomor Antrian</p>
          <div class="ticket-number">{{ ticket.number }}</div>
          <p class="ticket-service">{{ ticket.service_name }}</p>
          <p class="ticket-date">{{ formatDate(ticket.date) }}</p>
          <div class="struk-divider"></div>
          <div class="qr-wrapper">
            <QrcodeVue :value="qrUrl" :size="140" level="M" />
          </div>
          <p class="qr-hint">Scan untuk cek status antrian</p>
        </div>

        <div class="ticket-actions">
          <button class="btn btn-primary btn-lg" @click="printNormal" style="flex:1">
            <Printer :size="16" />
            Cetak Struk
          </button>
          <button class="btn btn-outline btn-lg" @click="printBluetooth" :disabled="printing" style="flex:1">
            <Loader2 v-if="printing" :size="16" class="spin" />
            <Bluetooth v-else :size="16" />
            {{ printing ? 'Mencetak...' : 'Bluetooth' }}
          </button>
        </div>
        <button class="btn btn-outline btn-lg" @click="resetTicket" style="width:100%;margin-top:10px">
          Ambil Lagi
        </button>

        <p v-if="btError" class="bt-error">{{ btError }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import QrcodeVue from 'qrcode.vue'
import { ChevronRight, CheckCircle2, Printer, Loader2, Bluetooth } from 'lucide-vue-next'

const services = ref([])
const ticket = ref(null)
const taking = ref(false)
const printing = ref(false)
const btError = ref('')
const settingName = ref('Antricu')
const settingAddress = ref('')

const qrUrl = computed(() => {
  if (!ticket.value) return ''
  return `${window.location.origin}/kiosk/status/${ticket.value.number}`
})

function formatDate(d) {
  return new Date(d).toLocaleDateString('id', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

async function fetchServices() {
  const res = await api.get('/api/v1/kiosk/services')
  services.value = res.data
}

async function fetchSetting() {
  try {
    const res = await api.get('/api/v1/kiosk/display')
    if (res.data.setting) {
      settingName.value = res.data.setting.institution_name || 'Antricu'
      settingAddress.value = res.data.setting.address || ''
    }
  } catch (e) { /* ignore */ }
}

async function takeQueue(serviceId) {
  taking.value = true
  try {
    const res = await api.post('/api/v1/kiosk/queue', { service_id: serviceId })
    ticket.value = res.data
  } finally { taking.value = false }
}

function resetTicket() {
  ticket.value = null
  btError.value = ''
}

function printNormal() {
  window.print()
}

async function printBluetooth() {
  printing.value = true
  btError.value = ''
  try {
    if (!navigator.bluetooth) {
      btError.value = 'Browser tidak mendukung Bluetooth. Gunakan Google Chrome.'
      return
    }

    const device = await navigator.bluetooth.requestDevice({
      filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
      optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'],
    })

    const server = await device.gatt.connect()
    const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb')
    const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb')

    const encoder = new TextEncoder()
    const ESC = '\x1B'
    const LF = '\x0A'
    const CENTER = ESC + '\x61\x01'
    const LEFT = ESC + '\x61\x00'
    const BOLD_ON = ESC + '\x45\x01'
    const BOLD_OFF = ESC + '\x45\x00'
    const DOUBLE_SIZE = ESC + '\x21\x30'
    const NORMAL_SIZE = ESC + '\x21\x00'
    const CUT = ESC + '\x69'

    let text = CENTER
    text += settingName.value + LF
    text += (settingAddress.value || '') + LF
    text += '================================' + LF
    text += LF
    text += BOLD_ON + DOUBLE_SIZE
    text += ticket.value.number + LF
    text += NORMAL_SIZE + BOLD_OFF + LF
    text += ticket.value.service_name + LF
    text += ticket.value.date + LF
    text += LF
    text += '================================' + LF
    text += 'Scan QR di struk untuk' + LF
    text += 'cek status antrian' + LF
    text += LF + LF + LF
    text += CUT

    const data = encoder.encode(text)
    const chunkSize = 20
    for (let i = 0; i < data.length; i += chunkSize) {
      const chunk = data.slice(i, i + chunkSize)
      await characteristic.writeValue(chunk)
    }

    await device.gatt.disconnect()
  } catch (e) {
    if (e.name !== 'NotFoundError') {
      btError.value = 'Gagal mencetak: ' + (e.message || 'Printer tidak ditemukan')
    }
  } finally { printing.value = false }
}

onMounted(() => { fetchServices(); fetchSetting() })
</script>

<style scoped>
.print-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
}
.kiosk-card {
  background: var(--bg-white);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 44px 36px;
  width: 100%;
  max-width: 440px;
  box-shadow: var(--shadow-xl);
}
.kiosk-header {
  text-align: center;
  margin-bottom: 32px;
}
.kiosk-header h1 {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--text);
  margin-top: 14px;
}
.kiosk-header p {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin-top: 2px;
}
.service-hint {
  font-size: 0.82rem;
  color: var(--text-secondary);
  text-align: center;
  margin-bottom: 16px;
}
.service-btn {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  padding: 16px;
  background: var(--bg-white);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  color: var(--text);
  margin-bottom: 10px;
  transition: var(--transition);
}
.service-btn:hover {
  border-color: var(--primary);
  background: var(--primary-subtle);
}
.service-btn:disabled { opacity: 0.5; }
.service-prefix {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: var(--primary);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.service-name {
  font-weight: 500;
  font-size: 0.95rem;
  flex: 1;
  text-align: left;
}

.ticket-view { text-align: center; }
.ticket-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: var(--success-subtle);
  color: var(--success);
  border-radius: var(--radius-full);
  font-size: 0.82rem;
  font-weight: 500;
  margin-bottom: 20px;
}

.ticket-struk {
  background: #fafafa;
  border: 1px dashed var(--border);
  border-radius: var(--radius-sm);
  padding: 24px 20px;
  margin-bottom: 20px;
}
.struk-institution {
  font-weight: 700;
  font-size: 1rem;
  color: var(--text);
}
.struk-address {
  font-size: 0.78rem;
  color: var(--text-secondary);
  margin-top: 2px;
}
.struk-divider {
  border-top: 1px dashed var(--border);
  margin: 14px 0;
}
.struk-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 4px;
}
.ticket-number {
  font-size: 3.2rem;
  font-weight: 800;
  color: var(--primary);
  line-height: 1;
  margin-bottom: 6px;
  letter-spacing: -1px;
}
.ticket-service {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text);
}
.ticket-date {
  font-size: 0.78rem;
  color: var(--text-secondary);
  margin-top: 2px;
}
.qr-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 4px;
}
.qr-hint {
  font-size: 0.72rem;
  color: var(--text-light);
  margin-top: 8px;
}

.ticket-actions {
  display: flex;
  gap: 10px;
}
.bt-error {
  margin-top: 12px;
  padding: 10px;
  background: var(--danger-subtle);
  color: var(--danger);
  border-radius: var(--radius-sm);
  font-size: 0.82rem;
}
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

@media print {
  @page {
    size: 80mm auto;
    margin: 4mm;
  }
  
  html, body {
    visibility: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  
  .print-page,
  .kiosk-card,
  .ticket-view,
  .ticket-struk,
  .ticket-struk * {
    visibility: visible !important;
  }

  .print-page {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    min-height: auto !important;
    background: white !important;
    padding: 0 !important;
    display: block !important;
  }

  .kiosk-card {
    position: static !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    max-width: none !important;
    border-radius: 0 !important;
    background: white !important;
  }

  .ticket-struk {
    border: none !important;
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  .ticket-number {
    color: black !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .kiosk-header,
  .ticket-badge,
  .ticket-actions,
  .bt-error,
  .btn,
  .service-list,
  .service-hint {
    display: none !important;
    visibility: hidden !important;
  }
}
</style>
