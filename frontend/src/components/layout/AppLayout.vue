<template>
  <div class="app-layout">
    <Sidebar :collapsed="sidebarCollapsed" />
    <div class="main-area" :class="{ collapsed: sidebarCollapsed }">
      <header class="top-bar">
        <div class="top-bar-left">
          <button class="toggle-btn" @click="sidebarCollapsed = !sidebarCollapsed">
            <PanelLeftClose v-if="!sidebarCollapsed" :size="20" />
            <PanelLeftOpen v-else :size="20" />
          </button>
          <h1 class="page-title">{{ pageTitle }}</h1>
        </div>
        <div class="top-bar-right">
          <span class="role-badge">{{ authStore.user?.role }}</span>
        </div>
      </header>
      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from './Sidebar.vue'
import { useAuthStore } from '../../stores/authStore'
import { PanelLeftClose, PanelLeftOpen } from 'lucide-vue-next'

const authStore = useAuthStore()
const sidebarCollapsed = ref(false)
const route = useRoute()

const titles = {
  '/queue-call': 'Loket Panggilan',
  '/counters': 'Menejemen Loket',
  '/queues': 'Daftar Antrian',
  '/services': 'Menejemen Layanan',
  '/settings': 'Pengaturan',
  '/users': 'Menejemen Pengguna',
}
const pageTitle = computed(() => titles[route.path] || 'Dashboard')
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
}
.main-area {
  flex: 1;
  margin-left: 260px;
  transition: margin-left 0.25s ease;
  display: flex;
  flex-direction: column;
}
.main-area.collapsed { margin-left: 68px; }

.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  height: 60px;
  background: var(--bg-white);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 50;
}
.top-bar-left {
  display: flex;
  align-items: center;
  gap: 16px;
}
.toggle-btn {
  background: none;
  color: var(--text-secondary);
  padding: 6px;
  border-radius: var(--radius-xs);
  display: flex;
  align-items: center;
}
.toggle-btn:hover {
  background: var(--bg);
  color: var(--text);
}
.page-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--text);
}
.top-bar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}
.role-badge {
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  background: var(--primary-subtle);
  color: var(--primary);
}
.page-content {
  padding: 24px 28px;
  flex: 1;
}
</style>
