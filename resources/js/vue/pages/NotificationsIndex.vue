<script setup>
import { Head, router } from '@inertiajs/vue3'
defineProps({ notifications: Object })
function markRead(id) { router.post(`/notifications/${id}/read`) }
function markAllRead() { router.post('/notifications/read-all') }
function destroy(id) { router.delete(`/notifications/${id}`) }
</script>

<template>
  <Head title="Notifications" />
  <div class="container mx-auto max-w-3xl px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Notifications</h1>
      <button @click="markAllRead" class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg">Mark All Read</button>
    </div>
    <div v-if="notifications.data.length === 0" class="text-center py-8 text-gray-500">No notifications.</div>
    <div v-else class="space-y-2">
      <div v-for="n in notifications.data" :key="n.id" :class="['p-4 rounded-lg border', n.read_at ? 'border-gray-200 bg-white dark:bg-gray-800' : 'border-blue-200 bg-blue-50 dark:bg-blue-900/20']">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm">{{ n.data?.message || n.type }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ n.created_at_human }}</p>
          </div>
          <div class="flex gap-1">
            <button v-if="!n.read_at" @click="markRead(n.id)" class="px-2 py-1 text-xs bg-gray-200 rounded">Read</button>
            <button @click="destroy(n.id)" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
