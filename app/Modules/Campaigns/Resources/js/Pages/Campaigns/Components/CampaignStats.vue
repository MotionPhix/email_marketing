<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  campaign: {
    total_recipients: number
    sent_count: number
    opened_count: number
    clicked_count: number
    bounced_count: number
    complained_count: number
  }
}

const props = defineProps<Props>()

const stats = computed(() => [
  {
    name: 'Recipients',
    value: props.campaign.total_recipients,
    color: 'text-blue-600',
  },
  {
    name: 'Sent',
    value: props.campaign.sent_count,
    color: 'text-green-600',
  },
  {
    name: 'Opens',
    value: props.campaign.opened_count,
    color: 'text-yellow-600',
  },
  {
    name: 'Clicks',
    value: props.campaign.clicked_count,
    color: 'text-purple-600',
  },
  {
    name: 'Bounces',
    value: props.campaign.bounced_count,
    color: 'text-red-600',
  },
  {
    name: 'Complaints',
    value: props.campaign.complained_count,
    color: 'text-orange-600',
  },
])
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Campaign Statistics</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div
          v-for="stat in stats"
          :key="stat.name"
          class="flex flex-col items-center p-4 space-y-2"
        >
          <span class="text-sm font-medium text-gray-600">{{ stat.name }}</span>
          <span :class="[stat.color, 'text-2xl font-semibold']">{{ stat.value }}</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
