<script setup lang="ts">
import {computed} from 'vue';

const {data} = defineProps<{
  data: object
}>();

const chartOptions = computed(() => ({
  chart: {
    zoom: false,
    id: 'campaign-stats-chart',
    toolbar: {show: true},
  },
  stroke: {
    // curve: 'smooth'
  },
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'dark',
      gradientToColors: ['#FDD835'],
      shadeIntensity: 1,
      type: 'horizontal',
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100, 100, 100]
    },
  },
  xaxis: {
    categories: Object.keys(data),
  },
}));

const chartSeries = computed(() => {
  return [
    {
      name: 'Opened',
      data: Object.values(data).map(item => item.open),
    },
    {
      name: 'Clicked',
      data: Object.values(data).map(item => item.click),
    },
    {
      name: 'Bounced',
      data: Object.values(data).map(item => item.bounce),
    },
    {
      name: 'Spam Report',
      data: Object.values(data).map(item => item.spam_report),
    },
    {
      name: 'Delivered',
      data: Object.values(data).map(item => item.delivered),
    }
  ];
});
</script>

<template>
  <div>
    <apexchart type="line" :options="chartOptions" :series="chartSeries"/>
  </div>
</template>
