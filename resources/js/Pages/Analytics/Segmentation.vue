<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { ApexOptions } from 'apexcharts'

const props = defineProps<{
  segments: Array<{
    id: number
    name: string
    total: number
    engagement: {
      high: number
      medium: number
      low: number
    }
    trends: {
      dates: string[]
      growth: number[]
      engagement: number[]
    }
    demographics: {
      age: Record<string, number>
      gender: Record<string, number>
      location: Record<string, number>
    }
    behavior: {
      openTime: Record<string, number>
      device: Record<string, number>
      interests: Record<string, number>
    }
  }>,
  schedulePerformance: {
    timeSlots: string[]
    openRates: number[]
    clickRates: number[]
    bestTimes: Array<{
      day: string
      time: string
      openRate: number
      clickRate: number
    }>
  },
  contentPerformance: {
    subjectLines: Array<{
      text: string
      openRate: number
      clickRate: number
      sentiment: number
    }>,
    contentTypes: Array<{
      type: string
      engagement: number
      timeSpent: number
      retention: number
    }>
  }
}>()

const selectedSegment = ref(props.segments[0]?.id)

// Engagement Distribution Chart
const engagementChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'inherit'
  },
  labels: ['High', 'Medium', 'Low'],
  colors: ['#22c55e', '#f59e0b', '#ef4444'],
  legend: {
    position: 'bottom',
    labels: {
      colors: 'var(--muted-foreground)'
    }
  },
  tooltip: {
    theme: 'dark'
  },
  plotOptions: {
    pie: {
      donut: {
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            formatter: (w) => {
              const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              return `${total} subscribers`
            }
          }
        }
      }
    }
  }
}))

// Time Slot Performance Chart
const timeSlotChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'area',
    stacked: false,
    toolbar: {
      show: false
    },
    fontFamily: 'inherit'
  },
  stroke: {
    curve: 'smooth',
    width: [2, 2]
  },
  colors: ['#0ea5e9', '#8b5cf6'],
  fill: {
    type: 'gradient',
    gradient: {
      opacityFrom: 0.4,
      opacityTo: 0.1
    }
  },
  xaxis: {
    categories: props.schedulePerformance.timeSlots,
    labels: {
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  },
  yaxis: {
    labels: {
      formatter: (val) => `${val}%`,
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  }
}))

const timeSlotSeries = computed(() => [
  {
    name: 'Open Rate',
    data: props.schedulePerformance.openRates
  },
  {
    name: 'Click Rate',
    data: props.schedulePerformance.clickRates
  }
])

// Content Performance Chart
const contentChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'radar',
    fontFamily: 'inherit',
    toolbar: {
      show: false
    }
  },
  colors: ['#0ea5e9', '#8b5cf6', '#22c55e'],
  markers: {
    size: 4
  },
  xaxis: {
    categories: ['Engagement', 'Time Spent', 'Retention'],
    labels: {
      style: {
        colors: 'var(--muted-foreground)'
      }
    }
  }
}))

const contentSeries = computed(() =>
  props.contentPerformance.contentTypes.map(type => ({
    name: type.type,
    data: [type.engagement, type.timeSpent, type.retention]
  }))
)
</script>

<template>
  <AppLayout title="Segmentation Analytics">
    <Head title="Segmentation Analytics" />

    <div class="container space-y-8 p-4 md:p-8">
      <!-- Header with Segment Selection -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-3xl font-bold tracking-tight">Segmentation Analytics</h2>
          <p class="text-muted-foreground">
            Analyze subscriber segments and engagement patterns
          </p>
        </div>

        <Select v-model="selectedSegment">
          <SelectTrigger class="w-[200px]">
            <SelectValue placeholder="Select segment" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="segment in segments"
              :key="segment.id"
              :value="segment.id"
            >
              {{ segment.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Engagement Overview -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader>
            <CardTitle>Total Subscribers</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ selectedSegmentData?.total }}
            </div>
            <p class="text-xs text-muted-foreground">
              In selected segment
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Engagement Rate</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ ((selectedSegmentData?.engagement.high / selectedSegmentData?.total) * 100).toFixed(1) }}%
            </div>
            <Progress
              :value="(selectedSegmentData?.engagement.high / selectedSegmentData?.total) * 100"
              class="mt-2"
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Growth Rate</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ getGrowthRate(selectedSegmentData?.trends.growth) }}%
            </div>
            <p class="text-xs text-muted-foreground">
              Last 30 days
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Avg. Open Rate</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ getAverageOpenRate(selectedSegmentData?.trends.engagement) }}%
            </div>
            <Progress
              :value="getAverageOpenRate(selectedSegmentData?.trends.engagement)"
              class="mt-2"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Engagement Distribution -->
      <Card>
        <CardHeader>
          <CardTitle>Engagement Distribution</CardTitle>
          <CardDescription>
            Subscriber engagement levels in the selected segment
          </CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="donut"
            height="300"
            :options="engagementChartOptions"
            :series="[
              selectedSegmentData?.engagement.high,
              selectedSegmentData?.engagement.medium,
              selectedSegmentData?.engagement.low
            ]"
          />
        </CardContent>
      </Card>

      <!-- Send Time Performance -->
      <Card>
        <CardHeader>
          <CardTitle>Send Time Performance</CardTitle>
          <CardDescription>
            Engagement rates by time of day
          </CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="area"
            height="300"
            :options="timeSlotChartOptions"
            :series="timeSlotSeries"
          />

          <div class="mt-4 space-y-4">
            <h4 class="text-sm font-medium">Best Performing Times</h4>
            <div class="grid gap-4 md:grid-cols-3">
              <div
                v-for="time in schedulePerformance.bestTimes"
                :key="time.day"
                class="rounded-lg border p-3"
              >
                <div class="font-medium">{{ time.day }}</div>
                <div class="mt-1 text-2xl font-bold">{{ time.time }}</div>
                <div class="mt-1 text-sm text-muted-foreground">
                  {{ time.openRate }}% open rate
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Content Performance -->
      <Card>
        <CardHeader>
          <CardTitle>Content Performance</CardTitle>
          <CardDescription>
            Analysis of different content types and their performance
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-8 md:grid-cols-2">
            <div>
              <apexchart
                type="radar"
                height="350"
                :options="contentChartOptions"
                :series="contentSeries"
              />
            </div>
            <div class="space-y-4">
              <h4 class="font-medium">Top Performing Subject Lines</h4>
              <div class="space-y-2">
                <div
                  v-for="subject in contentPerformance.subjectLines.slice(0, 5)"
                  :key="subject.text"
                  class="flex items-center justify-between rounded-lg border p-3"
                >
                  <div>
                    <div class="font-medium">{{ subject.text }}</div>
                    <div class="text-sm text-muted-foreground">
                      {{ subject.openRate }}% open rate
                    </div>
                  </div>
                  <Badge :variant="getSentimentVariant(subject.sentiment)">
                    {{ getSentimentLabel(subject.sentiment) }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
