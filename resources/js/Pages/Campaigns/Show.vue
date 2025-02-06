<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Badge } from '@/Components/ui/badge'
import { Separator } from '@/Components/ui/separator'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import VueApexCharts from 'vue3-apexcharts'
import { formatDistanceToNow, format } from 'date-fns'

interface Props {
  campaign: {
    title: string
    status: string
    template: {
      name: string
    }
    audience: {
      name: string
      recipient_count: number
    }
    created_at: string
    scheduled_at: string | null
    sent_at: string | null
  }
  stats: {
    summary: {
      total_recipients: number
      sent: number
      opened: number
      clicked: number
      bounced: number
      spam_reports: number
      unsubscribed: number
    }
    rates: {
      open_rate: number
      click_rate: number
      bounce_rate: number
      unsubscribe_rate: number
    }
    email_clients: Record<string, number>
    locations: Record<string, number>
    clicks: Record<string, {
      count: number
      last_clicked: string
    }>
    timeline: Record<string, {
      opens: number
      clicks: number
      bounces: number
      unsubscribes: number
    }>
  }
  filters: {
    start_date: string
    end_date: string
  }
  recent_events: Array<{
    type: string
    timestamp: string
    email: string
    ip: string
    user_agent: string
    url?: string
    reason?: string
  }>
}

const props = defineProps<Props>()

const activeTab = ref('overview')

// Timeline Chart Options
const timelineChartOptions = computed(() => ({
  chart: {
    type: 'area',
    height: 350,
    toolbar: {
      show: false
    },
    zoom: {
      enabled: false
    }
  },
  // Make the chart more responsive
  responsive: [{
    breakpoint: 640,
    options: {
      legend: {
        position: 'bottom',
        offsetY: 0,
        height: 50
      },
      xaxis: {
        labels: {
          rotate: -45,
          maxHeight: 60
        }
      }
    }
  }],
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: 2
  },
  colors: ['#22c55e', '#3b82f6', '#ef4444', '#f59e0b'],
  fill: {
    type: 'gradient',
    gradient: {
      opacityFrom: 0.6,
      opacityTo: 0.1
    }
  },
  xaxis: {
    categories: Object.keys(props.stats.timeline).map(date =>
      format(new Date(date), 'MMM dd')
    ),
    type: 'datetime'
  },
  yaxis: {
    labels: {
      formatter: (value: number) => Math.round(value)
    }
  },
  legend: {
    position: 'top'
  },
  series: [
    {
      name: 'Opens',
      data: Object.values(props.stats.timeline).map(day => day.opens)
    },
    {
      name: 'Clicks',
      data: Object.values(props.stats.timeline).map(day => day.clicks)
    },
    {
      name: 'Bounces',
      data: Object.values(props.stats.timeline).map(day => day.bounces)
    },
    {
      name: 'Unsubscribes',
      data: Object.values(props.stats.timeline).map(day => day.unsubscribes)
    }
  ]
}))

// Email Clients Donut Chart Options
const emailClientsChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 350
  },
  responsive: [{
    breakpoint: 640,
    options: {
      legend: {
        position: 'bottom',
        offsetY: 0,
        height: 50
      },
      plotOptions: {
        pie: {
          donut: {
            size: '65%'
          }
        }
      }
    }
  }],
  colors: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
  labels: Object.keys(props.stats.email_clients),
  series: Object.values(props.stats.email_clients),
  legend: {
    position: 'bottom'
  },
  plotOptions: {
    pie: {
      donut: {
        size: '70%'
      }
    }
  }
}))

// Click Distribution Chart Options
const clickDistributionChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    height: 350,
    toolbar: {
      show: false
    }
  },
  responsive: [{
    breakpoint: 640,
    options: {
      plotOptions: {
        bar: {
          horizontal: false,
          borderRadius: 2
        }
      },
      xaxis: {
        labels: {
          rotate: -45,
          maxHeight: 60
        }
      }
    }
  }],
  plotOptions: {
    bar: {
      borderRadius: 4,
      horizontal: true
    }
  },
  colors: ['#3b82f6'],
  xaxis: {
    categories: Object.keys(props.stats.clicks).map(url =>
      url.length > 40 ? url.substring(0, 40) + '...' : url
    )
  },
  series: [{
    name: 'Clicks',
    data: Object.values(props.stats.clicks).map(data => data.count)
  }]
}))

const getStatusColor = (status: string) => {
  switch (status.toLowerCase()) {
    case 'draft': return 'bg-gray-500'
    case 'scheduled': return 'bg-blue-500'
    case 'sending': return 'bg-yellow-500'
    case 'sent': return 'bg-green-500'
    case 'failed': return 'bg-red-500'
    default: return 'bg-gray-500'
  }
}

const getEventIcon = (type: string) => {
  switch (type) {
    case 'open': return '👁️'
    case 'click': return '🔗'
    case 'bounce': return '↩️'
    case 'unsubscribe': return '✖️'
    case 'spam': return '⚠️'
    default: return '📧'
  }
}
</script>

<template>
  <AppLayout>
    <Head :title="campaign.title" />

    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            {{ campaign.title }}
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Created {{ formatDistanceToNow(new Date(campaign.created_at), { addSuffix: true }) }}
          </p>
        </div>
        <Badge :class="getStatusColor(campaign.status)">
          {{ campaign.status }}
        </Badge>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Campaign Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <Card>
            <CardHeader>
              <CardTitle>Template</CardTitle>
            </CardHeader>
            <CardContent>
              <p>{{ campaign.template.name }}</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Audience</CardTitle>
            </CardHeader>
            <CardContent>
              <p>{{ campaign.audience.name }}</p>
              <p class="text-sm text-gray-500">{{ campaign.audience.recipient_count }} recipients</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Schedule</CardTitle>
            </CardHeader>
            <CardContent>
              <p v-if="campaign.scheduled_at">
                Scheduled for {{ format(new Date(campaign.scheduled_at), 'PPP') }}
              </p>
              <p v-else-if="campaign.sent_at">
                Sent {{ formatDistanceToNow(new Date(campaign.sent_at), { addSuffix: true }) }}
              </p>
              <p v-else>Not scheduled</p>
            </CardContent>
          </Card>
        </div>

        <!-- Stats Tabs -->
        <Card>
          <CardContent class="p-6">
            <Tabs v-model="activeTab">
              <TabsList class="mb-6">
                <TabsTrigger value="overview">Overview</TabsTrigger>
                <TabsTrigger value="engagement">Engagement</TabsTrigger>
                <TabsTrigger value="clicks">Click Analysis</TabsTrigger>
                <TabsTrigger value="events">Recent Events</TabsTrigger>
              </TabsList>

              <TabsContent value="overview">
                <!-- Summary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                  <Card v-for="(value, key) in stats.summary" :key="key">
                    <CardHeader>
                      <CardTitle class="capitalize">{{ key.replace('_', ' ') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div class="text-2xl font-bold">{{ value }}</div>
                      <div v-if="stats.rates[`${key}_rate`]" class="text-sm text-gray-500">
                        {{ stats.rates[`${key}_rate`] }}% Rate
                      </div>
                    </CardContent>
                  </Card>
                </div>

                <!-- Email Clients Chart -->
                <Card class="mb-6">
                  <CardHeader>
                    <CardTitle>Email Clients</CardTitle>
                    <CardDescription>Distribution of email clients used</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <VueApexCharts
                      type="donut"
                      height="350"
                      :options="emailClientsChartOptions"
                      :series="emailClientsChartOptions.series"
                    />
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="engagement">
                <!-- Engagement Timeline Chart -->
                <Card>
                  <CardHeader>
                    <CardTitle>Engagement Timeline</CardTitle>
                    <CardDescription>Campaign performance over time</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <VueApexCharts
                      type="area"
                      height="350"
                      :options="timelineChartOptions"
                      :series="timelineChartOptions.series"
                    />
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="clicks">
                <!-- Click Distribution Chart -->
                <Card>
                  <CardHeader>
                    <CardTitle>Click Distribution</CardTitle>
                    <CardDescription>Most clicked links in your campaign</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <VueApexCharts
                      type="bar"
                      height="350"
                      :options="clickDistributionChartOptions"
                      :series="clickDistributionChartOptions.series"
                    />
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="events">
                <!-- Recent Events Table -->
                <Card>
                  <CardHeader>
                    <CardTitle>Recent Events</CardTitle>
                    <CardDescription>Latest activity from your campaign</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Event</TableHead>
                          <TableHead>Email</TableHead>
                          <TableHead>Time</TableHead>
                          <TableHead>Details</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        <TableRow v-for="event in recent_events" :key="`${event.email}-${event.timestamp}`">
                          <TableCell>
                            <span class="flex items-center gap-2">
                              {{ getEventIcon(event.type) }}
                              <span class="capitalize">{{ event.type }}</span>
                            </span>
                          </TableCell>
                          <TableCell>{{ event.email }}</TableCell>
                          <TableCell>
                            {{ formatDistanceToNow(new Date(event.timestamp), { addSuffix: true }) }}
                          </TableCell>
                          <TableCell>
                            <span v-if="event.url" class="text-sm text-blue-500 truncate max-w-xs block">
                              {{ event.url }}
                            </span>
                            <span v-if="event.reason" class="text-sm text-red-500">
                              {{ event.reason }}
                            </span>
                          </TableCell>
                        </TableRow>
                      </TableBody>
                    </Table>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="events">
                <Card>
                  <CardHeader>
                    <CardTitle>Recent Events</CardTitle>
                    <CardDescription>Latest activity from your campaign</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <!-- Table for large screens -->
                    <div class="hidden md:block">
                      <Table>
                        <TableHeader>
                          <TableRow>
                            <TableHead>Event</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Time</TableHead>
                            <TableHead>Details</TableHead>
                          </TableRow>
                        </TableHeader>

                        <TableBody>
                          <TableRow v-for="event in recent_events" :key="`${event.email}-${event.timestamp}`">
                            <TableCell>
                              <span class="flex items-center gap-2">
                                {{ getEventIcon(event.type) }}
                                <span class="capitalize">{{ event.type }}</span>
                              </span>
                            </TableCell>
                            <TableCell>{{ event.email }}</TableCell>
                            <TableCell>
                              {{ formatDistanceToNow(new Date(event.timestamp), { addSuffix: true }) }}
                            </TableCell>
                            <TableCell>
                              <span v-if="event.url" class="text-sm text-blue-500 truncate max-w-xs block">
                                {{ event.url }}
                              </span>
                              <span v-if="event.reason" class="text-sm text-red-500">
                                {{ event.reason }}
                              </span>
                            </TableCell>
                          </TableRow>
                        </TableBody>
                      </Table>
                    </div>

                    <!-- Cards for mobile screens -->
                    <div class="md:hidden space-y-4">
                      <Card v-for="event in recent_events" :key="`${event.email}-${event.timestamp}`" class="bg-muted/50">
                        <CardContent class="p-4">
                          <div class="flex items-center justify-between mb-2">
                            <span class="flex items-center gap-2">
                              {{ getEventIcon(event.type) }}
                              <span class="capitalize font-medium">{{ event.type }}</span>
                            </span>
                            <span class="text-sm text-muted-foreground">
                              {{ formatDistanceToNow(new Date(event.timestamp), { addSuffix: true }) }}
                            </span>
                          </div>

                          <div class="space-y-1">
                            <p class="text-sm">
                              <span class="text-muted-foreground">Email:</span>
                              <span class="font-medium">{{ event.email }}</span>
                            </p>

                            <p v-if="event.url" class="text-sm text-blue-500 break-all">
                              {{ event.url }}
                            </p>

                            <p v-if="event.reason" class="text-sm text-red-500">
                              {{ event.reason }}
                            </p>
                          </div>
                        </CardContent>
                      </Card>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <!-- Let's also make the stats cards more responsive -->
              <TabsContent value="overview">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                  <Card v-for="(value, key) in stats.summary" :key="key">
                    <CardHeader class="p-4 md:p-6">
                      <CardTitle class="text-sm md:text-base capitalize">
                        {{ key.replace('_', ' ') }}
                      </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 pt-0 md:p-6 md:pt-0">
                      <div class="text-xl md:text-2xl font-bold">{{ value }}</div>
                      <div v-if="stats.rates[`${key}_rate`]" class="text-xs md:text-sm text-muted-foreground">
                        {{ stats.rates[`${key}_rate`] }}% Rate
                      </div>
                    </CardContent>
                  </Card>
                </div>

                <!-- Charts need to be responsive too -->
                <Card class="mb-6">
                  <CardHeader>
                    <CardTitle class="text-base md:text-lg">Email Clients</CardTitle>
                    <CardDescription class="text-sm">Distribution of email clients used</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div class="h-[300px] md:h-[350px]">
                      <VueApexCharts
                        type="donut"
                        :height="'100%'"
                        :options="emailClientsChartOptions"
                        :series="emailClientsChartOptions.series"
                      />
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="engagement">
                <Card>
                  <CardHeader>
                    <CardTitle class="text-base md:text-lg">Engagement Timeline</CardTitle>
                    <CardDescription class="text-sm">Campaign performance over time</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div class="h-[300px] md:h-[350px] mt-4">
                      <VueApexCharts
                        type="area"
                        :height="'100%'"
                        :options="timelineChartOptions"
                        :series="timelineChartOptions.series"
                      />
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="clicks">
                <Card>
                  <CardHeader>
                    <CardTitle class="text-base md:text-lg">Click Distribution</CardTitle>
                    <CardDescription class="text-sm">Most clicked links in your campaign</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div class="h-[300px] md:h-[350px]">
                      <VueApexCharts
                        type="bar"
                        :height="'100%'"
                        :options="clickDistributionChartOptions"
                        :series="clickDistributionChartOptions.series"
                      />
                    </div>

                    <!-- Mobile-friendly link list -->
                    <div class="mt-6 md:hidden">
                      <div class="space-y-4">
                        <div v-for="(data, url) in stats.clicks" :key="url"
                             class="p-4 rounded-lg bg-muted/50">
                          <div class="flex items-center justify-between mb-1">
                            <span class="font-medium">{{ data.count }} clicks</span>
                            <span class="text-sm text-muted-foreground">
                              {{ formatDistanceToNow(new Date(data.last_clicked), { addSuffix: true }) }}
                            </span>
                          </div>
                          <a :href="url"
                             target="_blank"
                             class="text-sm text-blue-500 break-all">
                            {{ url }}
                          </a>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Add some responsive styles */
@media (max-width: 640px) {
  :deep(.apexcharts-legend-series) {
    margin: 2px 5px !important;
  }

  :deep(.apexcharts-legend) {
    padding: 0 !important;
  }

  :deep(.apexcharts-text) {
    font-size: 11px !important;
  }
}

/* Ensure tabs are scrollable on mobile */
:deep(.tabs-list) {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

:deep(.tabs-list::-webkit-scrollbar) {
  display: none;
}
</style>
