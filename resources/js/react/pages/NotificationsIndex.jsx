import React from 'react'
import { Head, router } from '@inertiajs/react'

export default function NotificationsIndex({ notifications }) {
  return (
    <>
      <Head title="Notifications" />
      <div className="container mx-auto max-w-3xl px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Notifications</h1>
          <button onClick={() => router.post('/notifications/read-all')} className="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg">Mark All Read</button>
        </div>
        {notifications.data.length === 0 ? (
          <div className="text-center py-8 text-gray-500">No notifications.</div>
        ) : (
          <div className="space-y-2">
            {notifications.data.map(n => (
              <div key={n.id} className={`p-4 rounded-lg border ${n.read_at ? 'border-gray-200 bg-white dark:bg-gray-800' : 'border-blue-200 bg-blue-50'}`}>
                <div className="flex items-start justify-between">
                  <div>
                    <p className="text-sm">{n.data?.message || n.type}</p>
                    <p className="text-xs text-gray-500 mt-1">{n.created_at_human}</p>
                  </div>
                  <div className="flex gap-1">
                    {!n.read_at && <button onClick={() => router.post(`/notifications/${n.id}/read`)} className="px-2 py-1 text-xs bg-gray-200 rounded">Read</button>}
                    <button onClick={() => router.delete(`/notifications/${n.id}`)} className="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Delete</button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  )
}
