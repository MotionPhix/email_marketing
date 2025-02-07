<script setup lang="ts">
import type { CampaignStats } from '../../../types'
import {
  MailIcon,
  SendIcon,
  ClockIcon,
  PencilIcon
} from '@heroicons/vue/24/outline'

defineProps<{
  stats: CampaignStats
}>()

const stats = [
  {
    name: 'Total Campaigns',
    value: 'total',
    icon: MailIcon,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
  },
  {
    name: 'Sent',
    value: 'sent',
    icon: SendIcon,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
  },
  {
    name: 'Scheduled',
    value: 'scheduled',
    icon: ClockIcon,
    color: 'text-yellow-600',
    bgColor: 'bg-yellow-50',
  },
  {
    name: 'Draft',
    value: 'draft',
    icon: PencilIcon,
    color: 'text-gray-600',
    bgColor: 'bg-gray-50',
  },
]
</script>

<template>
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <Card v-for="item in stats" :key="item.name">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          {{ item.name }}
        </CardTitle>
        <component
          :is="item.icon"
          class="h-4 w-4"
          :class="item.color"
        />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ stats[item.value] }}</div>
      </CardContent>
    </Card>
  </div>
</template>
