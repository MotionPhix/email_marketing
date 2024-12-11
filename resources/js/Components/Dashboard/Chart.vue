<script setup lang="ts">
import { ref, watchEffect } from 'vue'

const { chartData } = defineProps<{
  chartData: Record<string, Record<string, number>>
}>()

const chartSeries = ref([])
const chartOptions = ref({
  chart: {
    type: 'line',
    height: 350,
    zoom: false,
  },
  xaxis: {
    categories: []
  },
  stroke: {
    curve: 'smooth',
    width: 2
  },
  dataLabels: {
    enabled: false
  },
  colors: ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#5d4037'], // Colors for each event series
  markers: {
    size: 4,
    colors: ['#6366F1'],
    strokeColors: '#fff',
  }
})

watchEffect(() => {
  // Extract all the unique dates from the chartData to unify the x-axis
  const allDates = new Set()
  Object.values(chartData).forEach(eventData => {
    Object.keys(eventData).forEach(date => allDates.add(date))
  })

  // Sort the dates in ascending order
  const sortedDates = Array.from(allDates).sort()

  // Update chart options for X-axis categories
  chartOptions.value.xaxis.categories = sortedDates

  // Create series for each event type
  chartSeries.value = Object.entries(chartData).map(([event, data]) => ({
    name: event.charAt(0).toUpperCase() + event.slice(1), // Capitalize event name
    data: sortedDates.map(date => data[date] || 0) // Fill missing dates with 0
  }))
})
</script>

<template>
  <div>
    <apexchart
      :options="chartOptions"
      :series="chartSeries" />
  </div>
</template>
