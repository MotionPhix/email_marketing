<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { CampaignStats } from '@/types/campaign'
import { ApexOptions } from 'apexcharts'
import { useCampaignStats } from '@/composables/useCampaignStats'
import { Icon } from '@tabler/icons-vue'
import { computed } from 'vue'

const props = defineProps<{
  campaignId: string
}>()

const {
  stats,
  loading,
  error,
  refresh,
  deliveryRate,
  openRate,
  clickRate,
  bounceRate,
} = useCampaignStats(props.campaignId)

const chartOptions = ref<ApexOptions>({
  chart: {
    type: 'area',
    height: 350,
    toolbar: {
      show: false,
    },
    fontFamily: 'inherit',
  },
  theme: {
    mode: 'light',
  },
  stroke: {
    curve: 'smooth',
    width: 3,
  },
  colors: ['#0ea5e9', '#6366f1'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [50, 100, 100]
    },
  },
  xaxis: {
    type: 'datetime',
    labels: {
      style: {
        cssClass: 'text-xs font-normal',
      },
    },
  },
  yaxis: {
    labels: {
      style: {
        cssClass: 'text-xs font-normal',
      },
      formatter: (value) => Math.round(value),
    },
  },
  tooltip: {
    x: {
      format: 'dd MMM yyyy',
    },
  },
  grid: {
    borderColor: 'rgba(0,0,0,0.1)',
  },
})

const geoChartOptions = ref<ApexOptions>({
  chart: {
    type: 'bar',
    height: 350,
    fontFamily: 'inherit',
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 2,
      barHeight: '70%',
    },
  },
  colors: ['#0ea5e9'],
  grid: {
    borderColor: 'rgba(0,0,0,0.1)',
  },
  xaxis: {
    labels: {
      style: {
        cssClass: 'text-xs font-normal',
      },
    },
  },
  yaxis: {
    labels: {
      style: {
        cssClass: 'text-xs font-normal',
      },
    },
  },
})

const engagementSeries = computed(() => [
  {
    name: 'Opens',
    data: stats.value?.timeStats.map(stat => ({
      x: stat.timestamp,
      y: stat.opens,
    })) || [],
  },
  {
    name: 'Clicks',
    data: stats.value?.timeStats.map(stat => ({
      x: stat.timestamp,
      y: stat.clicks,
    })) || [],
  },
])
</script>

<template>
  <div class="space-y-8">
    <!-- Summary Cards -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card class="overflow-hidden">
        <CardHeader class="space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Open Rate</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.openRate.toFixed(1) }}%
          </div>
          <div class="text-xs text-muted-foreground">
            {{ stats?.opens }} opens
          </div>
        </CardContent>
      </Card>

      <Card class="overflow-hidden">
        <CardHeader class="space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Click Rate</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.clickRate.toFixed(1) }}%
          </div>
          <div class="text-xs text-muted-foreground">
            {{ stats?.clicks }} clicks
          </div>
        </CardContent>
      </Card>

      <Card class="overflow-hidden">
        <CardHeader class="space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Delivery Rate</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats?.deliveryRate.toFixed(1) }}%
          </div>
          <div class="text-xs text-muted-foreground">
            {{ stats?.recipients }} recipients
          </div>
        </CardContent>
      </Card>

      <Card class="overflow-hidden">
        <CardHeader class="space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Bounce Rate</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ ((stats?.bounces || 0) / (stats?.recipients || 1) * 100).toFixed(1) }}%
          </div>
          <div class="text-xs text-muted-foreground">
            {{ stats?.bounces }} bounces
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Charts -->
    <div class="grid gap-4 md:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Engagement Over Time</CardTitle>
          <CardDescription>
            Track opens and clicks over the campaign duration
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="loading" class="flex h-[350px] items-center justify-center">
            <Icon name="loader-2" class="h-8 w-8 animate-spin" />
          </div>
          <VueApexCharts
            v-else
            :options="chartOptions"
            :series="engagementSeries"
            height="350"
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Geographic Distribution</CardTitle>
          <CardDescription>
            Opens by country
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="loading" class="flex h-[350px] items-center justify-center">
            <Icon name="loader-2" class="h-8 w-8 animate-spin" />
          </div>
          <VueApexCharts
            v-else
            :options="geoChartOptions"
            :series="[{
              name: 'Opens',
              data: stats?.geoStats.map(stat => ({
                x: stat.country,
                y: stat.opens,
              })) || []
            }]"
            height="350"
          />
        </CardContent>
      </Card>
    </div>

    <!-- Detailed Stats Table -->
    <Card>
      <CardHeader>
        <CardTitle>Detailed Performance</CardTitle>
        <CardDescription>
          Comprehensive view of campaign metrics
        </CardDescription>
      </CardHeader>
      <CardContent>
        <table class="w-full">
          <thead>
          <tr class="border-b">
            <th class="pb-2 text-left font-medium">Metric</th>
            <th class="pb-2 text-right font-medium">Count</th>
            <th class="pb-2 text-right font-medium">Rate</th>
          </tr>
          </thead>
          <tbody>
          <tr class="border-b">
            <td class="py-2">Opens</td>
            <td class="py-2 text-right">{{ stats?.opens }}</td>
            <td class="py-2 text-right">{{ stats?.openRate.toFixed(1) }}%</td>
          </tr>
          <tr class="border-b">
            <td class="py-2">Clicks</td>
            <td class="py-2 text-right">{{ stats?.clicks }}</td>
            <td class="py-2 text-right">{{ stats?.clickRate.toFixed(1) }}%</td>
          </tr>
          <tr class="border-b">
            <td class="py-2">Bounces</td>
            <td class="py-2 text-right">{{ stats?.bounces }}</td>
            <td class="py-2 text-right">
              {{ ((stats?.bounces || 0) / (stats?.recipients || 1) * 100).toFixed(1) }}%
            </td>
          </tr>
          <tr class="border-b">
            <td class="py-2">Unsubscribes</td>
            <td class="py-2 text-right">{{ stats?.unsubscribes }}</td>
            <td class="py-2 text-right">
              {{ ((stats?.unsubscribes || 0) / (stats?.recipients || 1) * 100).toFixed(1) }}%
            </td>
          </tr>
          <tr>
            <td class="py-2">Complaints</td>
            <td class="py-2 text-right">{{ stats?.complaints }}</td>
            <td class="py-2 text-right">
              {{ ((stats?.complaints || 0) / (stats?.recipients || 1) * 100).toFixed(1) }}%
            </td>
          </tr>
          </tbody>
        </table>
      </CardContent>
    </Card>
  </div>
</template>
