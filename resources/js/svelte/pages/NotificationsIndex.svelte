<script>
  import { router } from '@inertiajs/svelte'
  export let notifications
  function markRead(id) { router.post(`/notifications/${id}/read`) }
  function markAllRead() { router.post('/notifications/read-all') }
  function destroy(id) { router.delete(`/notifications/${id}`) }
</script>

<svelte:head><title>Notifications</title></svelte:head>

<div class="container mx-auto max-w-3xl px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Notifications</h1>
    <button on:click={markAllRead} class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg">Mark All Read</button>
  </div>
  {#if notifications.data.length === 0}
    <div class="text-center py-8 text-gray-500">No notifications.</div>
  {:else}
    {#each notifications.data as n (n.id)}
      <div class="p-4 rounded-lg border mb-2 {n.read_at ? 'border-gray-200 bg-white' : 'border-blue-200 bg-blue-50'}">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm">{n.data?.message || n.type}</p>
            <p class="text-xs text-gray-500 mt-1">{n.created_at_human}</p>
          </div>
          <div class="flex gap-1">
            {#if !n.read_at}<button on:click={() => markRead(n.id)} class="px-2 py-1 text-xs bg-gray-200 rounded">Read</button>{/if}
            <button on:click={() => destroy(n.id)} class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Delete</button>
          </div>
        </div>
      </div>
    {/each}
  {/if}
</div>
