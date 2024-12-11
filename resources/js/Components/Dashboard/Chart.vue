<script setup lang="ts">
import { ref, watchEffect } from 'vue'

const {chartData} = defineProps<{
  chartData: object
}>()

const chartSeries = ref([])
const chartOptions = ref({
  chart: {
    type: 'line',
    height: 350
  },
  xaxis: {
    categories: []
  },
  stroke: {
    curve: 'smooth'
  },
  dataLabels: {
    enabled: false
  },
  colors: ['#6366F1'],
  markers: {
    size: 4,
    colors: ['#6366F1'],
    strokeColors: '#fff',
    strokeWidth: 2
  }
})

watchEffect(() => {
  chartSeries.value = [{
    name: 'Emails Sent',
    data: Object.values(chartData)
  }]
  chartOptions.value.xaxis.categories = Object.keys(chartData)
})
</script>

<template>
  <div>
    <apexchart
      type="line"
      :options="chartOptions"
      :series="chartSeries" />
  </div>
</template>
