<script setup lang="ts">
import {computed, ref} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Calendar } from "@/Components/ui/v-calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover"
import {
  MailCheck,
  MailX,
  MousePointerClick,
  Eye,
  AlertTriangle,
  TrendingUp,
  Users,
  Mail,
  Clock,
  Activity,
  ArrowUpRight,
  ArrowDownRight,
  CalendarIcon
} from 'lucide-vue-next'
import Chart from '@/Components/Dashboard/Chart.vue'
import {Button} from '@/Components/ui/button'
import {Progress} from '@/Components/ui/progress'
import {format} from "date-fns";
import {router} from "@inertiajs/vue3";

// Types
interface EmailEvent {
  event: string
  reason?: string
  email_log_id: number
  timestamp: string
  email_log: {
    id: number
    email: string
  }
}

interface Props {
  stats: {
    total_recipients: number
    total_campaigns: number
    total_sent: number
    delivered: { count: number; percentage: number }
    opened: { count: number; percentage: number }
    clicked: { count: number; percentage: number }
    bounced: { count: number; percentage: number }
    spam: { count: number; percentage: number }
    unsubscribed: { count: number; percentage: number }
  }
  chartData: Record<string, Record<string, number>>
  recentEvents: Array<{
    event: string
    email_log_id: number
    timestamp: string
    email_log: {
      id: number
      email: string
    }
  }>
  periodComparison: {
    delivered: number
    opened: number
    clicked: number
    bounced: number
  }
  filters: {
    period: string
    start_date: string
    end_date: string
  }
}

/*interface Props {
  stats: {
    total_recipients: number
    total_campaigns: number
    total_sent: number
    delivered: { count: number; percentage: number }
    opened: { count: number; percentage: number }
    clicked: { count: number; percentage: number }
    bounced: { count: number; percentage: number }
    spam: { count: number; percentage: number }
    unsubscribed: { count: number; percentage: number }
  }
  chartData: Record<string, Record<string, number>>
  recentEvents: EmailEvent[]
  periodComparison: {
    delivered: number
    opened: number
    clicked: number
    bounced: number
  }
}*/

const props = defineProps<Props>()

// Computed
const deliverabilityScore = computed(() => {
  const {delivered, bounced} = props.stats
  return Math.round((delivered?.count / (delivered?.count + bounced?.count)) * 100)
})

const engagementScore = computed(() => {
  const {opened, clicked, bounced, spam} = props.stats
  return Math.round(
    ((opened?.percentage + clicked?.percentage) - (bounced?.percentage + spam?.percentage)) * 10
  )
})

const getMetricTrend = (current: number, previous: number) => {
  const difference = ((current - previous) / previous) * 100
  return {
    direction: difference >= 0 ? 'up' : 'down',
    percentage: Math.abs(Math.round(difference))
  }
}

// Methods
const formatNumber = (num: number): string => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num?.toString()
}

const getStatusColor = (status: string): string => {
  const colors = {
    delivered: 'text-green-600',
    opened: 'text-blue-600',
    clicked: 'text-indigo-600',
    bounced: 'text-red-600',
    spam: 'text-yellow-600'
  }
  return colors[status] || 'text-gray-600'
}

const formatDate = (timestamp: string): string => {
  const date = new Date(timestamp)
  return new Intl.DateTimeFormat('default', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

// Period options
const periodOptions = [
  { value: '7d', label: 'Last 7 days' },
  { value: '30d', label: 'Last 30 days' },
  { value: '90d', label: 'Last 90 days' },
  { value: '1y', label: 'Last year' },
  { value: 'custom', label: 'Custom range' },
]

// State
const selectedPeriod = ref(props.filters.period)
const dateRange = ref({
  start: props.filters.start_date ? new Date(props.filters.start_date) : null,
  end: props.filters.end_date ? new Date(props.filters.end_date) : null,
})
const isCustomRange = ref(selectedPeriod.value === 'custom')

// Methods
const handlePeriodChange = (value: string) => {
  selectedPeriod.value = value
  isCustomRange.value = value === 'custom'

  if (value !== 'custom') {
    // Reset custom date range when switching to preset periods
    dateRange.value = { start: null, end: null }
    updateFilters()
  }
}

const handleDateRangeChange = (range: { start: Date | null; end: Date | null }) => {
  dateRange.value = range
  if (range.start && range.end) {
    updateFilters()
  }
}

const updateFilters = () => {
  const params: Record<string, string> = {
    period: selectedPeriod.value
  }

  if (selectedPeriod.value === 'custom' && dateRange.value.start && dateRange.value.end) {
    params.start_date = format(dateRange.value.start, 'yyyy-MM-dd')
    params.end_date = format(dateRange.value.end, 'yyyy-MM-dd')
  }

  router.get(route('dashboard'), params, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

// Format date for display
const formatDisplaydDate = (date: Date | null) => {
  if (!date) return ''
  return format(date, 'PP')
}
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        Dashboard
      </h2>
    </template>

    <div class="mt-12 mb-6 px-6 lg:px-0">
      <Card>
        <CardHeader>
          <CardTitle>Filter Analytics</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
            <!-- Period Select -->
            <div class="w-full sm:w-48">
              <Select v-model="selectedPeriod" @update:modelValue="handlePeriodChange">
                <SelectTrigger>
                  <SelectValue :placeholder="periodOptions.find(p => p.value === selectedPeriod)?.label" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="option in periodOptions"
                    :key="option.value"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Custom Date Range -->
            <div v-if="isCustomRange" class="flex-1">
              <Popover>
                <PopoverTrigger as-child>
                  <Button variant="outline" class="w-full justify-start text-left font-normal">
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    <span v-if="dateRange.start && dateRange.end">
                    {{ formatDisplayDate(dateRange.start) }} - {{ formatDisplayDate(dateRange.end) }}
                  </span>
                    <span v-else>Pick a date range</span>
                  </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0" align="start">
                  <Calendar
                    v-model="dateRange"
                    mode="range"
                    :disabled-dates="{ after: new Date() }"
                    class="rounded-md border"
                    @update:model-value="handleDateRangeChange"
                  />
                </PopoverContent>
              </Popover>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="py-6 px-4 lg:px-0">
      <!-- Overview Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Deliverability Score -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Deliverability Score
            </CardTitle>
            <MailCheck class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>

          <CardContent>
            <div class="flex items-center space-x-2">
              <div class="text-2xl font-bold">{{ deliverabilityScore }}%</div>
              <span
                class="text-xs"
                :class="getMetricTrend(stats.delivered?.percentage, periodComparison?.delivered).direction === 'up'
                  ? 'text-green-600'
                  : 'text-red-600'"
              >
                <component
                  :is="getMetricTrend(stats.delivered?.percentage, periodComparison?.delivered).direction === 'up'
                    ? ArrowUpRight
                    : ArrowDownRight"
                  class="h-4 w-4 inline"
                />
                {{ getMetricTrend(stats.delivered?.percentage, periodComparison?.delivered).percentage }}%
              </span>
            </div>
            <Progress
              :value="deliverabilityScore"
              class="mt-2"
            />
          </CardContent>
        </Card>

        <!-- Engagement Score -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Engagement Score
            </CardTitle>
            <TrendingUp class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="flex items-center space-x-2">
              <div class="text-2xl font-bold">{{ engagementScore }}/100</div>
              <span
                class="text-xs"
                :class="getMetricTrend(stats.opened?.percentage, periodComparison?.opened).direction === 'up'
                  ? 'text-green-600'
                  : 'text-red-600'"
              >
                <component
                  :is="getMetricTrend(stats.opened?.percentage, periodComparison?.opened).direction === 'up'
                    ? ArrowUpRight
                    : ArrowDownRight"
                  class="h-4 w-4 inline"
                />
                {{ getMetricTrend(stats.opened?.percentage, periodComparison?.opened).percentage }}%
              </span>
            </div>
            <Progress
              :value="engagementScore"
              class="mt-2"
            />
          </CardContent>
        </Card>

        <!-- Total Recipients -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Recipients
            </CardTitle>
            <Users class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ formatNumber(stats?.total_recipients) }}
            </div>
            <p class="text-xs text-muted-foreground">
              Across {{ stats.total_campaigns }} campaigns
            </p>
          </CardContent>
        </Card>

        <!-- Total Sent -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Sent
            </CardTitle>
            <Mail class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ formatNumber(stats?.total_sent) }}
            </div>
            <p class="text-xs text-muted-foreground">
              {{ formatNumber(stats.delivered?.count) }} delivered
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content -->
      <div class="grid gap-4 lg:grid-cols-7">
        <!-- Chart Section -->
        <Card class="lg:col-span-4">
          <CardHeader>
            <CardTitle>Performance Overview</CardTitle>
            <CardDescription>
              Email campaign metrics over time
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Chart :chart-data="chartData"/>
          </CardContent>
        </Card>

        <!-- Activity Feed -->
        <Card class="lg:col-span-3">
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>
              Latest email events
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="event in recentEvents"
                :key="`${event.email_log_id}-${event.timestamp}`"
                class="flex flex-col space-y-1"
              >
                <div class="flex items-center space-x-2">
                  <Activity class="h-4 w-4" :class="getStatusColor(event.event)"/>
                  <span class="text-sm font-medium capitalize">
                    {{ event.event }}
                  </span>
                  <span class="text-xs text-muted-foreground">
                    {{ formatDate(event.timestamp) }}
                  </span>
                </div>
                <p class="text-xs text-muted-foreground truncate">
                  {{ event.email_log.email }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Detailed Metrics -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mt-6">
        <!-- Opens -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Opens</CardTitle>
            <Eye class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ stats.opened?.percentage }}%
            </div>
            <p class="text-xs text-muted-foreground">
              {{ formatNumber(stats.opened?.count) }} total opens
            </p>
            <div class="mt-4">
              <Progress :value="stats.opened?.percentage"/>
            </div>
          </CardContent>
        </Card>

        <!-- Clicks -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Clicks</CardTitle>
            <MousePointerClick class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ stats.clicked?.percentage }}%
            </div>
            <p class="text-xs text-muted-foreground">
              {{ formatNumber(stats.clicked?.count) }} total clicks
            </p>
            <div class="mt-4">
              <Progress :value="stats.clicked?.percentage"/>
            </div>
          </CardContent>
        </Card>

        <!-- Bounces -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Bounces</CardTitle>
            <MailX class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ stats.bounced?.percentage }}%
            </div>
            <p class="text-xs text-muted-foreground">
              {{ formatNumber(stats.bounced?.count) }} total bounces
            </p>
            <div class="mt-4">
              <Progress :value="stats.bounced?.percentage"/>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.grid {
  @apply gap-4;
}

:deep(.apexcharts-tooltip) {
  @apply bg-background border rounded-lg shadow-lg !important;
}

:deep(.apexcharts-tooltip-title) {
  @apply bg-muted border-b !important;
}

.date-range-input {
  @apply px-3 py-2 rounded-md border border-input bg-background text-sm ring-offset-background
  file:border-0 file:bg-transparent file:text-sm file:font-medium
  placeholder:text-muted-foreground focus-visible:outline-none
  focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2
  disabled:cursor-not-allowed disabled:opacity-50;
}
</style>
