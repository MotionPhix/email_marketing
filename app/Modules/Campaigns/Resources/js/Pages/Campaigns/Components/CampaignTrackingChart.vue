<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend } from 'recharts'

interface Props {
  campaignId: string
}

const props = defineProps<Props>()
const trackingData = ref([])

const fetchTrackingData = async () => {
  try {
    const response = await fetch(`/api/campaigns/${props.campaignId}/tracking-data`)
    trackingData.value = await response.json()
  } catch (error) {
    console.error('Error fetching tracking data:', error)
  }
}

onMounted(() => {
  fetchTrackingData()
})
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Campaign Performance</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="w-full overflow-x-auto">
        <LineChart width={800} height={400} :data="trackingData">
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Line type="monotone" dataKey="opens" stroke="#FCD34D" />
          <Line type="monotone" dataKey="clicks" stroke="#8B5CF6" />
          <Line type="monotone" dataKey="bounces" stroke="#EF4444" />
        </LineChart>
      </div>
    </CardContent>
  </Card>
</template>
