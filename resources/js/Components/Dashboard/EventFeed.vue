<script setup>
import {ref} from 'vue'
import dayjs from 'dayjs'

defineProps({
  events: {
    type: Array,
    required: true
  }
})

const formatDate = (date) => dayjs(date).format('D MMM, YYYY h:mm A')

const badgeClass = (eventType) => {
  switch (eventType) {
    case 'delivered':
      return 'bg-green-100 text-green-800 px-2 py-1 rounded'
    case 'bounce':
      return 'bg-red-100 text-red-800 px-2 py-1 rounded'
    case 'click':
      return 'bg-blue-100 text-blue-800 px-2 py-1 rounded'
    case 'open':
      return 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded'
    default:
      return 'bg-gray-100 text-gray-800 px-2 py-1 rounded'
  }
}
</script>

<template>
  <div class="space-y-4 divide-y">
    <div
      v-for="event in events" :key="event.id"
      class="flex items-center justify-between pt-3">
      <div class="flex items-center space-x-2">
        <span :class="badgeClass(event.event)">{{ event.event }}</span>
        <span class="text-sm text-gray-700">{{ event.email_log.email }}</span>
      </div>
      <span class="text-sm text-gray-500">{{ formatDate(event.timestamp) }}</span>
    </div>
  </div>
</template>
