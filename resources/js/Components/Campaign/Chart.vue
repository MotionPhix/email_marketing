<script setup lang="ts">
import { computed } from 'vue'
import { useDark } from "@vueuse/core"
import { Eye, MousePointer, ArrowUpCircle, AlertTriangle, CheckCircle } from 'lucide-react'

interface ChartDataPoint {
  open: number
  click: number
  bounce: number
  spam_report: number
  delivered: number
}

interface ChartData {
  [date: string]: ChartDataPoint
}

interface Props {
  data: ChartData
  height?: number | string
  title?: string
}

const props = withDefaults(defineProps<Props>(), {
  height: 350,
  title: 'Performance Over Time'
})

const isDark = useDark()

const formatTooltip = (date: string) => {
  const dataPoint = props.data[date];
  const formattedDate = new Date(date).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });

  return `
    <div class="custom-tooltip ${isDark.value ? 'dark' : 'light'}">
      <div class="tooltip-header">
        <div class="tooltip-title">${formattedDate}</div>
      </div>
      <div class="tooltip-content">
        <div class="metrics-container">
          <div class="metric">
            <div class="metric-icon opens">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </div>
            <div class="metric-info">
              <div class="metric-value">${dataPoint.open.toLocaleString()}</div>
              <div class="metric-label">Opens</div>
            </div>
          </div>
          <div class="metric">
            <div class="metric-icon clicks">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 7h3a5 5 0 0 1 5 5 5 5 0 0 1-5 5h-3m-6 0H6a5 5 0 0 1-5-5 5 5 0 0 1 5-5h3"></path>
                <line x1="8" y1="12" x2="16" y2="12"></line>
              </svg>
            </div>
            <div class="metric-info">
              <div class="metric-value">${dataPoint.click.toLocaleString()}</div>
              <div class="metric-label">Clicks</div>
            </div>
          </div>
          <div class="metric">
            <div class="metric-icon bounces">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="m15 9-6 6"></path>
                <path d="m9 9 6 6"></path>
              </svg>
            </div>
            <div class="metric-info">
              <div class="metric-value">${dataPoint.bounce.toLocaleString()}</div>
              <div class="metric-label">Bounces</div>
            </div>
          </div>
          <div class="metric">
            <div class="metric-icon spam">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
            </div>
            <div class="metric-info">
              <div class="metric-value">${dataPoint.spam_report.toLocaleString()}</div>
              <div class="metric-label">Spam</div>
            </div>
          </div>
          <div class="metric">
            <div class="metric-icon delivered">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
            </div>
            <div class="metric-info">
              <div class="metric-value">${dataPoint.delivered.toLocaleString()}</div>
              <div class="metric-label">Delivered</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
};

const chartSeries = computed(() => [
  {
    name: 'Opens',
    data: Object.values(props.data).map(item => item.open)
  },
  {
    name: 'Clicks',
    data: Object.values(props.data).map(item => item.click)
  },
  {
    name: 'Bounces',
    data: Object.values(props.data).map(item => item.bounce)
  },
  {
    name: 'Spam Reports',
    data: Object.values(props.data).map(item => item.spam_report)
  },
  {
    name: 'Delivered',
    data: Object.values(props.data).map(item => item.delivered)
  }
]);

const chartOptions = computed(() => ({
  chart: {
    type: 'line',
    zoom: { enabled: false },
    toolbar: {
      show: true,
      tools: {
        download: true,
        selection: false,
        zoom: false,
        zoomin: false,
        zoomout: false,
        pan: false,
      }
    },
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800,
    },
    background: 'transparent',
  },
  stroke: {
    curve: 'smooth',
    width: 2,
    lineCap: 'round',
  },
  colors: [
    'rgb(37 99 235)', // opens
    'rgb(22 163 74)', // clicks
    'rgb(220 38 38)', // bounces
    'rgb(217 119 6)', // spam
    'rgb(101 163 13)', // delivered
  ],
  grid: {
    borderColor: isDark.value ? 'rgb(55 65 81)' : 'rgb(229 231 235)',
    strokeDashArray: 4,
    xaxis: { lines: { show: true } },
    yaxis: { lines: { show: true } },
  },
  xaxis: {
    categories: Object.keys(props.data),
    labels: {
      style: {
        colors: isDark.value ? 'rgb(156 163 175)' : 'rgb(107 114 128)',
        fontFamily: 'inherit',
      },
    },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: {
        colors: isDark.value ? 'rgb(156 163 175)' : 'rgb(107 114 128)',
        fontFamily: 'inherit',
      }
    },
  },
  tooltip: {
    enabled: true,
    shared: true,
    followCursor: true,
    custom: ({ dataPointIndex }) => {
      const date = Object.keys(props.data)[dataPointIndex];
      return formatTooltip(date);
    },
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right',
    labels: {
      colors: isDark.value ? 'rgb(243 244 246)' : 'rgb(17 24 39)',
    },
  },
  responsive: [
    {
      breakpoint: 640,
      options: {
        legend: {
          position: 'bottom',
          horizontalAlign: 'center',
        }
      }
    }
  ]
}));
</script>

<template>
  <div class="w-full">
    <apexchart
      type="line"
      :height="height"
      :options="chartOptions"
      :series="chartSeries"
    />
  </div>
</template>

<style>
.custom-tooltip {
  background: rgb(255 255 255 / 0.95);
  backdrop-filter: blur(8px);
  border: 1px solid rgb(229 231 235);
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  padding: 1rem;
  min-width: 320px;
}

.dark .custom-tooltip {
  background: rgb(17 24 39 / 0.95);
  border-color: rgb(55 65 81);
}

.tooltip-header {
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgb(229 231 235);
}

.dark .tooltip-header {
  border-color: rgb(55 65 81);
}

.tooltip-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(17 24 39);
}

.dark .tooltip-title {
  color: rgb(243 244 246);
}

.metrics-container {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.75rem;
}

.metric {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem;
  border-radius: 8px;
  background: rgb(249 250 251);
  transition: transform 0.2s ease;
}

.metric:hover {
  transform: translateY(-2px);
}

.dark .metric {
  background: rgb(31 41 55);
}

.metric-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  margin-bottom: 0.5rem;
}

.metric-icon.opens { background: rgb(219 234 254); color: rgb(37 99 235); }
.metric-icon.clicks { background: rgb(220 252 231); color: rgb(22 163 74); }
.metric-icon.bounces { background: rgb(254 226 226); color: rgb(220 38 38); }
.metric-icon.spam { background: rgb(254 243 199); color: rgb(217 119 6); }
.metric-icon.delivered { background: rgb(236 252 203); color: rgb(101 163 13); }

.dark .metric-icon.opens { background: rgb(30 58 138); color: rgb(147 197 253); }
.dark .metric-icon.clicks { background: rgb(20 83 45); color: rgb(134 239 172); }
.dark .metric-icon.bounces { background: rgb(127 29 29); color: rgb(252 165 165); }
.dark .metric-icon.spam { background: rgb(120 53 15); color: rgb(253 224 71); }
.dark .metric-icon.delivered { background: rgb(54 83 20); color: rgb(190 242 100); }

.metric-info {
  text-align: center;
}

.metric-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(17 24 39);
  margin-bottom: 0.25rem;
}

.dark .metric-value {
  color: rgb(243 244 246);
}

.metric-label {
  font-size: 0.75rem;
  color: rgb(107 114 128);
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.dark .metric-label {
  color: rgb(156 163 175);
}
</style>
