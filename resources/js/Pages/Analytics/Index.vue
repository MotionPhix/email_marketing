<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { ApexOptions } from 'apexcharts'

defineProps<{
  subscriberStats: {
    total: number
    active: number
    unsubscribed: number
    bounced: number
    growth: number
    openRate: number
    clickRate: number
  },
  emailClients: {
    gmail: number
    outlook: number
    apple: number
    yahoo: number
    other: number
  },
  geoData: {
    countries: string[]
    counts: number[]
  },
  campaigns: Array<{
    id: number
    name: string
    sent: number
    opened: number
    clicked: number
    bounced: number
    createdAt: string
  }>,
  subscribers: {
    dates: string[]
    counts: number[]
  }
}>()

const activeTab = ref('overview')

// Subscriber Growth Chart
const growthChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'area',
    toolbar: {
      show: false
    },
    fontFamily: 'inherit'
  },
  grid: {
    borderColor: 'rgba(var(--border), 0.1)',
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  colors: ['#0ea5e9'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [50, 100, 100]
    }
  },
  xaxis: {
    categories: props.subscribers.dates,
    axisBorder: { show: false },
    labels: {
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  },
  tooltip: {
    theme: 'dark'
  }
}))

const growthChartSeries = computed(() => [{
  name: 'Subscribers',
  data: props.subscribers.counts
}])

// Email Clients Distribution
const emailClientsChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'inherit'
  },
  labels: ['Gmail', 'Outlook', 'Apple Mail', 'Yahoo', 'Other'],
  colors: ['#0ea5e9', '#8b5cf6', '#22c55e', '#f59e0b', '#64748b'],
  legend: {
    position: 'bottom',
    labels: {
      colors: 'var(--muted-foreground)'
    }
  },
  tooltip: {
    theme: 'dark'
  }
}))

const emailClientsChartSeries = computed(() => [
  props.emailClients.gmail,
  props.emailClients.outlook,
  props.emailClients.apple,
  props.emailClients.yahoo,
  props.emailClients.other
])

// Geographic Distribution
const geoChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'bar',
    toolbar: {
      show: false
    },
    fontFamily: 'inherit'
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 2
    }
  },
  colors: ['#0ea5e9'],
  xaxis: {
    categories: props.geoData.countries,
    labels: {
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  },
  tooltip: {
    theme: 'dark'
  }
}))

const geoChartSeries = computed(() => [{
  name: 'Subscribers',
  data: props.geoData.counts
}])
</script>

<template>
  <AppLayout title="Analytics">
    <Head title="Analytics" />

    <div class="container space-y-8 p-4 md:p-8">
      <div>
        <h2 class="text-3xl font-bold tracking-tight">Analytics</h2>
        <p class="text-muted-foreground">
          Detailed insights about your subscribers and campaigns
        </p>
      </div>

      <Tabs v-model="activeTab" class="space-y-8">
        <TabsList class="grid w-full grid-cols-4 lg:w-[600px]">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="engagement">Engagement</TabsTrigger>
          <TabsTrigger value="geography">Geography</TabsTrigger>
          <TabsTrigger value="devices">Devices</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" class="space-y-8">
          <!-- Key Metrics -->
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
              <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Total Subscribers</CardTitle>
                <Users class="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div class="text-2xl font-bold">{{ subscriberStats.total }}</div>
                <p class="text-xs text-muted-foreground">
                  <span :class="subscriberStats.growth >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ subscriberStats.growth >= 0 ? '+' : '' }}{{ subscriberStats.growth }}%
                  </span>
                  from last month
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Open Rate</CardTitle>
                <Mail class="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div class="text-2xl font-bold">{{ subscriberStats.openRate }}%</div>
                <Progress :value="subscriberStats.openRate" class="mt-2" />
              </CardContent>
            </Card>

            <Card>
              <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Click Rate</CardTitle>
                <MousePointerClick class="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div class="text-2xl font-bold">{{ subscriberStats.clickRate }}%</div>
                <Progress :value="subscriberStats.clickRate" class="mt-2" />
              </CardContent>
            </Card>

            <Card>
              <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Bounce Rate</CardTitle>
                <AlertTriangle class="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div class="text-2xl font-bold">
                  {{ (subscriberStats.bounced / subscriberStats.total * 100).toFixed(1) }}%
                </div>
                <Progress
                  :value="subscriberStats.bounced / subscriberStats.total * 100"
                  class="mt-2"
                />
              </CardContent>
            </Card>
          </div>

          <!-- Subscriber Growth Chart -->
          <Card>
            <CardHeader>
              <CardTitle>Subscriber Growth</CardTitle>
            </CardHeader>
            <CardContent>
              <apexchart
                type="area"
                height="350"
                :options="growthChartOptions"
                :series="growthChartSeries"
              />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="engagement" class="space-y-8">
          <!-- Campaign Performance Table -->
          <Card>
            <CardHeader>
              <CardTitle>Campaign Performance</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="relative overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                  <tr>
                    <th class="text-left p-4">Campaign</th>
                    <th class="text-left p-4">Sent</th>
                    <th class="text-left p-4">Open Rate</th>
                    <th class="text-left p-4">Click Rate</th>
                    <th class="text-left p-4">Bounce Rate</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="campaign in campaigns" :key="campaign.id">
                    <td class="p-4">{{ campaign.name }}</td>
                    <td class="p-4">{{ campaign.sent }}</td>
                    <td class="p-4">
                      {{ ((campaign.opened / campaign.sent) * 100).toFixed(1) }}%
                    </td>
                    <td class="p-4">
                      {{ ((campaign.clicked / campaign.sent) * 100).toFixed(1) }}%
                    </td>
                    <td class="p-4">
                      {{ ((campaign.bounced / campaign.sent) * 100).toFixed(1) }}%
                    </td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="geography" class="space-y-8">
          <!-- Geographic Distribution -->
          <Card>
            <CardHeader>
              <CardTitle>Geographic Distribution</CardTitle>
            </CardHeader>
            <CardContent>
              <apexchart
                type="bar"
                height="350"
                :options="geoChartOptions"
                :series="geoChartSeries"
              />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="devices" class="space-y-8">
          <!-- Email Clients -->
          <Card>
            <CardHeader>
              <CardTitle>Email Clients</CardTitle>
            </CardHeader>
            <CardContent>
              <apexchart
                type="donut"
                height="350"
                :options="emailClientsChartOptions"
                :series="emailClientsChartSeries"
              />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
