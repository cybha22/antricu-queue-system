<template>
  <div class="display-page">
    <div v-if="showAudioPrompt" class="audio-overlay" @click="unlockAudio">
      <div class="audio-prompt-box">
        <Volume2 :size="48" color="var(--primary)" style="margin-bottom:16px" />
        <h2 style="color: black">Klik Layar Untuk Memulai Display</h2>
        <p style="color: #4b5563">Sistem membutuhkan interaksi layar untuk mengaktifkan suara Text-to-Speech</p>
        <button class="btn btn-primary" style="margin-top:20px">Mulai Sistem</button>
      </div>
    </div>
    <header class="display-header">
      <div class="header-left">
        <div class="header-logo">
          <img src="/logo.svg" alt="Logo" width="36" height="36" />
        </div>
        <div>
          <h1>{{ setting?.institution_name || 'Antricu' }}</h1>
          <p>{{ setting?.address || 'Sistem Manajemen Antrian' }}</p>
        </div>
      </div>
      <div class="header-right">
        <button class="sound-toggle" @click="toggleSound" :title="soundEnabled ? 'Suara aktif' : 'Suara mati'">
          <Volume2 v-if="soundEnabled" :size="20" />
          <VolumeX v-else :size="20" />
        </button>
        <div class="header-time">{{ currentTime }}</div>
      </div>
    </header>

    <div class="display-grid">
      <div class="called-section">
        <h2>Sedang Dipanggil</h2>
        <div class="called-cards">
          <div v-for="(q, i) in calledQueues" :key="q.id" class="called-card" :class="{ latest: i === 0 }">
            <div class="called-number">{{ q.number }}</div>
            <div class="called-counter">{{ q.counter?.name }}</div>
            <div class="called-service">{{ q.service?.name }}</div>
          </div>
          <div v-if="!calledQueues.length" class="no-queue">
            <Monitor :size="48" color="#475569" />
            <span>Belum ada panggilan</span>
          </div>
        </div>
      </div>

      <div class="waiting-section">
        <h2>Antrian Menunggu</h2>
        <div class="waiting-list">
          <div v-for="s in services" :key="s.id" class="waiting-item">
            <div class="waiting-service">
              <span class="waiting-prefix">{{ s.prefix }}</span>
              <span>{{ s.name }}</span>
            </div>
            <span class="waiting-count">{{ s.queues_count }}</span>
          </div>
          <div v-if="!services.length" class="no-waiting">Tidak ada antrian</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../../services/api'
import { Monitor, Volume2, VolumeX } from 'lucide-vue-next'

const setting = ref(null)
const calledQueues = ref([])
const services = ref([])
const currentTime = ref('')
const soundEnabled = ref(false)
const showAudioPrompt = ref(true)
const lastCalledMap = ref({})
const voicesReady = ref(false)
let interval, timeInterval
let speakQueue = []
let isSpeaking = false

function loadVoices() {
  const voices = window.speechSynthesis.getVoices()
  if (voices.length > 0) {
    voicesReady.value = true
  }
}

function unlockAudio() {
  if (!('speechSynthesis' in window)) {
    soundEnabled.value = true
    showAudioPrompt.value = false
    return
  }
  
  window.speechSynthesis.cancel()
  
  const testUtterance = new SpeechSynthesisUtterance('Sistem antrian aktif')
  testUtterance.lang = 'id-ID'
  testUtterance.rate = 0.9
  testUtterance.volume = 1

  const voices = window.speechSynthesis.getVoices()
  const idVoice = voices.find(v => v.lang.startsWith('id'))
  if (idVoice) testUtterance.voice = idVoice
  
  testUtterance.onend = () => {
    soundEnabled.value = true
    showAudioPrompt.value = false
  }
  
  testUtterance.onerror = () => {
    soundEnabled.value = true
    showAudioPrompt.value = false
  }
  
  window.speechSynthesis.speak(testUtterance)
  
  setTimeout(() => {
    showAudioPrompt.value = false
    soundEnabled.value = true
  }, 3000)
}

function processQueue() {
  if (isSpeaking || speakQueue.length === 0) return
  if (!soundEnabled.value) { speakQueue = []; return }
  
  isSpeaking = true
  const text = speakQueue.shift()
  
  window.speechSynthesis.cancel()
  
  const utterance = new SpeechSynthesisUtterance(text)
  utterance.lang = 'id-ID'
  utterance.rate = 0.85
  utterance.pitch = 1
  utterance.volume = 1

  const voices = window.speechSynthesis.getVoices()
  const idVoice = voices.find(v => v.lang.startsWith('id'))
  if (idVoice) utterance.voice = idVoice

  utterance.onend = () => {
    isSpeaking = false
    setTimeout(processQueue, 300)
  }
  
  utterance.onerror = () => {
    isSpeaking = false
    setTimeout(processQueue, 300)
  }

  window.speechSynthesis.speak(utterance)
  
  setTimeout(() => {
    if (isSpeaking) {
      isSpeaking = false
      processQueue()
    }
  }, 10000)
}

function announceQueue(q) {
  if (!soundEnabled.value) return
  const chars = q.number.split('').join(' ')
  const text = `Nomor antrian ${chars}, silakan menuju ${q.counter?.name || 'loket'}`
  speakQueue.push(text)
  processQueue()
}

function toggleSound() {
  if (!soundEnabled.value) {
    unlockAudio()
  } else {
    soundEnabled.value = false
  }
}

async function fetchData() {
  try {
    const res = await api.get('/api/v1/kiosk/display')
    setting.value = res.data.setting
    services.value = res.data.services

    const newCalled = res.data.called_queues || []
    const prevMap = lastCalledMap.value
    const newMap = {}
    
    newCalled.forEach(q => {
      const key = q.id
      const calledAt = q.called_at || ''
      newMap[key] = calledAt
      
      if (prevMap[key] !== calledAt) {
        setTimeout(() => announceQueue(q), 500)
      }
    })
    
    lastCalledMap.value = newMap
    calledQueues.value = newCalled
  } catch (e) { /* ignore */ }
}

function updateTime() {
  currentTime.value = new Date().toLocaleTimeString('id', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

onMounted(() => {
  if ('speechSynthesis' in window) {
    loadVoices()
    window.speechSynthesis.onvoiceschanged = loadVoices
  }

  fetchData()
  interval = setInterval(fetchData, 5000)
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  clearInterval(interval)
  clearInterval(timeInterval)
  speakQueue = []
  if ('speechSynthesis' in window) {
    window.speechSynthesis.cancel()
  }
})
</script>

<style scoped>
.audio-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15,23,42,0.95);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.audio-prompt-box {
  background: white;
  padding: 40px;
  border-radius: 20px;
  text-align: center;
  max-width: 400px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.4);
  animation: slide-up 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes slide-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.display-page {
  min-height: 100vh;
  background: #0f172a;
  color: #f1f5f9;
  padding: 32px;
}
.display-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 36px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}
.display-header h1 { font-size: 1.5rem; font-weight: 700; }
.display-header p { color: #94a3b8; font-size: 0.85rem; }
.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.sound-toggle {
  background: rgba(255,255,255,0.08);
  border: none;
  color: #94a3b8;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.2s;
}
.sound-toggle:hover {
  background: rgba(255,255,255,0.15);
  color: #f1f5f9;
}
.header-time {
  font-size: 2.2rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: #818cf8;
}
.display-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 28px;
}
.called-section h2, .waiting-section h2 {
  font-size: 0.82rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #94a3b8;
  margin-bottom: 16px;
}
.called-cards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
.called-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  transition: all 0.3s;
}
.called-card.latest {
  border-color: #4f46e5;
  box-shadow: 0 0 24px rgba(79,70,229,0.15);
  animation: pulse-border 2s ease-in-out infinite;
}
.called-number {
  font-size: 3rem;
  font-weight: 800;
  color: #818cf8;
  line-height: 1.1;
}
.called-counter {
  font-size: 1.1rem;
  font-weight: 600;
  margin-top: 8px;
  color: #f1f5f9;
}
.called-service {
  font-size: 0.82rem;
  color: #94a3b8;
  margin-top: 2px;
}
.no-queue {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 60px;
  color: #64748b;
  font-size: 1rem;
}
.waiting-section {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 20px;
}
.waiting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #334155;
}
.waiting-item:last-child { border-bottom: none; }
.waiting-service { display: flex; align-items: center; gap: 10px; }
.waiting-prefix {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: rgba(79,70,229,0.15);
  color: #818cf8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.82rem;
}
.waiting-count {
  font-size: 1.4rem;
  font-weight: 700;
  color: #f59e0b;
}
.no-waiting {
  text-align: center;
  color: #64748b;
  padding: 20px;
  font-size: 0.85rem;
}

@keyframes pulse-border {
  0%, 100% { box-shadow: 0 0 24px rgba(79,70,229,0.15); }
  50% { box-shadow: 0 0 40px rgba(79,70,229,0.3); }
}
</style>
