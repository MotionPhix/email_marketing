<script setup lang="ts">
import { computed } from 'vue';
import { useDark } from '@vueuse/core';
import dayjs from 'dayjs';

interface ChartDataPoint {
  open: number;
  click: number;
  bounce: number;
  spam_report: number;
  delivered: number;
}

interface ChartData {
  [key: string]: ChartDataPoint;
}

const props = defineProps<{ data: ChartData }>();
const isDark = useDark();

const labelMap: Record<string, string> = {
  open: 'Open',
  click: 'Click',
  bounce: 'Bounce',
  spam_report: 'Spam Report',
  delivered: 'Delivered',
};

const chartSeries = computed(() => {
  const categories = Object.keys(props.data);
  const seriesKeys: (keyof ChartDataPoint)[] = ['open', 'click', 'bounce', 'spam_report', 'delivered'];

  return seriesKeys.map((key) => ({
    name: labelMap[key],
    data: categories.map((category) => props.data[category][key]),
  }));
});

const chartOptions = computed(() => ({
  chart: {
    type: 'line',
    height: 350,
    background: isDark.value ? '#1E1E1E' : '#FFFFFF',
    foreColor: isDark.value ? '#FFFFFF' : '#333333',
    zoom: false,
    toolbar: false
  },
  xaxis: {
    type: 'datetime',
    categories: Object.keys(props.data),
    labels: {
      style: {
        colors: isDark.value ? '#FFFFFF' : '#333333',
      },
    },
  },
  yaxis: {
    labels: {
      style: {
        colors: isDark.value ? '#FFFFFF' : '#333333',
      },
    },
  },
  stroke: {
    show: true,
    width: 2,
    curve: 'smooth',
  },
  markers: {
    size: 0,
    hover: {
      size: 0,
    },
  },
  dataLabels: {
    enabled: false,
  },
  tooltip: {
    theme: isDark.value ? 'dark' : 'light',
    shared: true,
    intersect: false,
    x: {
      formatter: function (val) {
        return dayjs(val).format('ddd D MMM, YY')
      }
    }
  },
}));
</script>

<template>
  <apexchart
    :options="chartOptions"
    :series="chartSeries"
    type="line"
    height="350"
  />
</template>
