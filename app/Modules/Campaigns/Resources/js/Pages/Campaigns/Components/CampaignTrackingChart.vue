<script setup lang="ts">
import {computed, nextTick, ref, watch} from 'vue'
import { useTheme } from '@/Composables/useTheme'

const { isDark } = useTheme()

interface Props {
  campaign: {
    id: string
    started_at: string | null
  }
  events: Array<{
    event_type: string
    created_at: string
  }>
}

const props = defineProps<Props>()

// Group events by date and type
const chartData = computed(() => {
  const startDate = props.campaign.started_at
    ? new Date(props.campaign.started_at)
    : new Date(Math.min(...props.events.map(e => new Date(e.created_at).getTime())))

  const endDate = new Date()
  const dates = []
  const current = new Date(startDate)

  while (current <= endDate) {
    dates.push(new Date(current))
    current.setDate(current.getDate() + 1)
  }

  const eventsByDate = dates.map(date => {
    const dayEvents = props.events.filter(event => {
      const eventDate = new Date(event.created_at)
      return eventDate.toDateString() === date.toDateString()
    })

    return {
      x: date.toISOString().split('T')[0],
      opens: dayEvents.filter(e => e.event_type === 'opened').length,
      clicks: dayEvents.filter(e => e.event_type === 'clicked').length,
      bounces: dayEvents.filter(e => e.event_type === 'bounced').length,
    }
  })

  return eventsByDate
})

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    height: '100%',
    toolbar: {
      show: false,
    },
    zoom: {
      enabled: false,
    },
    background: 'transparent',
    foreColor: isDark.value ? '#94a3b8' : '#64748b',
  },
  theme: {
    mode: isDark.value ? 'dark' : 'light',
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    curve: 'smooth',
    width: 2,
  },
  series: [
    {
      name: 'Opens',
      data: chartData.value.map(d => ({ x: d.x, y: d.opens })),
    },
    {
      name: 'Clicks',
      data: chartData.value.map(d => ({ x: d.x, y: d.clicks })),
    },
    {
      name: 'Bounces',
      data: chartData.value.map(d => ({ x: d.x, y: d.bounces })),
    },
  ],
  colors: isDark.value
    ? ['#34d399', '#818cf8', '#f87171'] // Dark mode colors
    : ['#10B981', '#6366F1', '#EF4444'], // Light mode colors
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: isDark.value ? 0.3 : 1,
      opacityFrom: 0.7,
      opacityTo: 0.3,
      stops: [0, 90, 100],
    },
  },
  grid: {
    borderColor: isDark.value ? '#334155' : '#e2e8f0',
    strokeDashArray: 4,
    xaxis: {
      lines: {
        show: true
      }
    },
    yaxis: {
      lines: {
        show: true
      }
    },
    padding: {
      top: 0,
      right: 0,
      bottom: 0,
      left: 10
    },
  },
  xaxis: {
    type: 'datetime',
    labels: {
      format: 'MMM dd',
      style: {
        colors: isDark.value ? '#94a3b8' : '#64748b',
      },
    },
    axisBorder: {
      color: isDark.value ? '#334155' : '#e2e8f0',
    },
    axisTicks: {
      color: isDark.value ? '#334155' : '#e2e8f0',
    },
  },
  yaxis: {
    labels: {
      formatter: (value: number) => Math.round(value),
      style: {
        colors: isDark.value ? '#94a3b8' : '#64748b',
      },
    },
  },
  tooltip: {
    theme: isDark.value ? 'dark' : 'light',
    x: {
      format: 'MMM dd, yyyy',
    },
    y: {
      formatter: (value: number) => Math.round(value),
    },
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right',
    labels: {
      colors: isDark.value ? '#94a3b8' : '#64748b',
    },
  },
  responsive: [
    {
      breakpoint: 640,
      options: {
        legend: {
          position: 'bottom',
          horizontalAlign: 'center',
        },
        chart: {
          height: 300,
        },
      },
    },
  ],
}))

// Watch for theme changes and update chart
watch(isDark, () => {
  // Force chart redraw when theme changes
  nextTick(() => {
    if (chartInstance.value) {
      chartInstance.value.updateOptions(chartOptions.value)
    }
  })
})

const chartInstance = ref(null)

const onChartMount = (chart) => {
  chartInstance.value = chart
}
</script>

<template>
  <Card>
    <CardHeader class="space-y-1">
      <CardTitle class="text-xl">Campaign Performance</CardTitle>
      <CardDescription>
        Track opens, clicks, and bounces over time
      </CardDescription>
    </CardHeader>
    <CardContent>
      <div class="h-[400px] w-full sm:h-[500px]">
        <ClientOnly>
          <apexchart
            ref="chart"
            :options="chartOptions"
            :series="chartOptions.series"
            @mounted="onChartMount"
            class="h-full w-full"
          />
        </ClientOnly>
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
:deep(.apexcharts-canvas) {
  background: transparent !important;
}

:deep(.apexcharts-tooltip) {
  @apply rounded-md border border-border bg-popover text-popover-foreground shadow-md;
}

:deep(.apexcharts-tooltip-title) {
  @apply border-b border-border bg-muted px-4 py-2 font-semibold;
}

:deep(.apexcharts-tooltip-series-group) {
  @apply px-4 py-2;
}

:deep(.apexcharts-legend-series) {
  @apply !inline-flex items-center gap-1;
}
</style>
