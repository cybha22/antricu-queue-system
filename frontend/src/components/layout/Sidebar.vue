<template>
  <aside class="sidebar" :class="{ collapsed }">
    <div class="sidebar-brand">
      <div class="brand-logo">
        <img src="/logo.svg" alt="Antricu Logo" width="28" height="28" />
      </div>
      <span v-if="!collapsed" class="brand-text">Antricu</span>
    </div>

    <nav class="sidebar-nav">
      <router-link
        v-for="item in menuItems"
        :key="item.path"
        :to="item.path"
        class="nav-item"
        :class="{ active: $route.path === item.path }"
      >
        <component :is="item.icon" :size="20" :stroke-width="1.8" />
        <span v-if="!collapsed" class="nav-label">{{ item.label }}</span>
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <div v-if="!collapsed" class="user-info">
        <div class="user-avatar">
          {{ authStore.user?.name?.charAt(0)?.toUpperCase() }}
        </div>
        <div class="user-detail">
          <span class="user-name">{{ authStore.user?.name }}</span>
          <span class="user-role">{{ authStore.user?.role }}</span>
        </div>
      </div>
      <button class="nav-item logout-btn" @click="handleLogout">
        <LogOut :size="20" :stroke-width="1.8" />
        <span v-if="!collapsed" class="nav-label">Keluar</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import {
  LayoutDashboard,
  Volume2,
  Building2,
  ClipboardList,
  Wrench,
  Settings,
  Users,
  BarChart3,
  LogOut,
} from 'lucide-vue-next'

defineProps({ collapsed: Boolean })
const router = useRouter()
const authStore = useAuthStore()

const menuItems = computed(() => {
  const items = []
  if (authStore.user?.role === 'admin') {
    items.push(
      { path: '/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
    )
  }
  items.push(
    { path: '/queue-call', icon: Volume2, label: 'Loket Panggilan' },
  )
  if (authStore.user?.role === 'admin') {
    items.push(
      { path: '/counters', icon: Building2, label: 'Manajemen Loket' },
      { path: '/queues', icon: ClipboardList, label: 'Daftar Antrian' },
      { path: '/services', icon: Wrench, label: 'Manajemen Layanan' },
      { path: '/reports', icon: BarChart3, label: 'Laporan' },
      { path: '/settings', icon: Settings, label: 'Pengaturan' },
      { path: '/users', icon: Users, label: 'Manajemen Pengguna' },
    )
  }
  return items
})

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  height: 100vh;
  background: var(--bg-sidebar);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 100;
  transition: width 0.25s ease;
}
.sidebar.collapsed { width: 68px; }

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.brand-logo {
  min-width: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.brand-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
  white-space: nowrap;
}

.sidebar-nav {
  flex: 1;
  padding: 12px 8px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  color: var(--text-sidebar);
  font-weight: 450;
  font-size: 0.875rem;
  transition: var(--transition);
  margin-bottom: 2px;
}
.nav-item:hover {
  background: var(--bg-sidebar-hover);
  color: var(--text-white);
}
.nav-item.active {
  background: var(--bg-sidebar-active);
  color: #fff;
}
.nav-item.active svg { color: var(--primary-light); }

.sidebar-footer {
  padding: 12px 8px;
  border-top: 1px solid rgba(255,255,255,0.08);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 14px;
  margin-bottom: 4px;
}
.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-xs);
  background: var(--primary);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.82rem;
}
.user-detail { display: flex; flex-direction: column; }
.user-name { color: var(--text-white); font-size: 0.82rem; font-weight: 500; }
.user-role { color: var(--text-sidebar); font-size: 0.72rem; text-transform: capitalize; }

.logout-btn {
  width: 100%;
  background: none;
  border: none;
  color: var(--text-sidebar);
}
.logout-btn:hover {
  background: rgba(239,68,68,0.1);
  color: var(--danger);
}
</style>
