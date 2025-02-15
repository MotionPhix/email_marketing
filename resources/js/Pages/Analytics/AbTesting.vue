<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { ApexOptions } from 'apexcharts'

const props = defineProps<{
  abTests: Array<{
    id: number
    name: string
    status: 'running' | 'completed' | 'draft'
    startDate: string
    endDate: string | null
    variants: Array<{
      id: number
      name: string
      sentCount: number
      openCount: number
      clickCount: number
      conversionCount: number
      revenueGenerated: number
    }>
  }>
  currentTest: {
    id: number
    metrics: {
      dates: string[]
      variantA: {
        opens: number[]
        clicks: number[]
        conversions: number[]
      }
      variantB: {
        opens: number[]
        clicks: number[]
        conversions: number[]
      }
    }
  }
}>()

const selectedTest = ref(props.abTests[0]?.id)

// Conversion Rate Comparison Chart
const conversionChartOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'bar',
    stacked: false,
    toolbar: {
      show: false
    },
    fontFamily: 'inherit'
  },
  plotOptions: {
    bar: {
      horizontal: false,
      borderRadius: 4,
      columnWidth: '45%'
    }
  },
  colors: ['#0ea5e9', '#8b5cf6'],
  xaxis: {
    categories: ['Opens', 'Clicks', 'Conversions'],
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
      },
      formatter: (value) => `${value}%`
    }
  },
  tooltip: {
    theme: 'dark',
    y: {
      formatter: (value) => `${value}%`
    }
  }
}))

const getConversionData = (variant: typeof props.abTests[0]['variants'][0]) => {
  const sentCount = variant.sentCount
  return [
    (variant.openCount / sentCount * 100).toFixed(1),
    (variant.clickCount / sentCount * 100).toFixed(1),
    (variant.conversionCount / sentCount * 100).toFixed(1)
  ]
}

const conversionChartSeries = computed(() => {
  const test = props.abTests.find(t => t.id === selectedTest.value)
  if (!test || test.variants.length < 2) return []

  return [
    {
      name: test.variants[0].name,
      data: getConversionData(test.variants[0])
    },
    {
      name: test.variants[1].name,
      data: getConversionData(test.variants[1])
    }
  ]
})

// Time Series Performance Chart
const timeSeriesOptions = computed<ApexOptions>(() => ({
  chart: {
    type: 'line',
    toolbar: {
      show: false
    },
    fontFamily: 'inherit'
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  colors: ['#0ea5e9', '#8b5cf6'],
  grid: {
    borderColor: 'rgba(var(--border), 0.1)',
  },
  xaxis: {
    categories: props.currentTest.metrics.dates,
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
      },
      formatter: (value) => `${value}%`
    }
  },
  tooltip: {
    theme: 'dark',
    y: {
      formatter: (value) => `${value}%`
    }
  }
}))

const timeSeriesSeries = computed(() => [
  {
    name: 'Variant A',
    data: props.currentTest.metrics.variantA.conversions
  },
  {
    name: 'Variant B',
    data: props.currentTest.metrics.variantB.conversions
  }
])
</script>

<template>
  <AppLayout title="A/B Testing Analytics">
    <Head title="A/B Testing Analytics" />

    <div class="container space-y-8 p-4 md:p-8">
      <div>
        <h2 class="text-3xl font-bold tracking-tight">A/B Testing Analytics</h2>
        <p class="text-muted-foreground">
          Compare and analyze your email campaign variants
        </p>
      </div>

      <!-- Test Selection -->
      <Card>
        <CardHeader>
          <CardTitle>Select Test Campaign</CardTitle>
        </CardHeader>
        <CardContent>
          <Select v-model="selectedTest">
            <SelectTrigger>
              <SelectValue placeholder="Choose a test campaign" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectLabel>Active Tests</SelectLabel>
                <SelectItem
                  v-for="test in abTests.filter(t => t.status === 'running')"
                  :key="test.id"
                  :value="test.id"
                >
                  {{ test.name }}
                </SelectItem>
              </SelectGroup>
              <SelectSeparator />
              <SelectGroup>
                <SelectLabel>Completed Tests</SelectLabel>
                <SelectItem
                  v-for="test in abTests.filter(t => t.status === 'completed')"
                  :key="test.id"
                  :value="test.id"
                >
                  {{ test.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </CardContent>
      </Card>

      <!-- Performance Metrics -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card v-for="variant in selectedTestVariants" :key="variant.id">
          <CardHeader class="space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              {{ variant.name }}
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <div>
              <div class="text-2xl font-bold">
                {{ (variant.conversionCount / variant.sentCount * 100).toFixed(1) }}%
              </div>
              <p class="text-xs text-muted-foreground">Conversion Rate</p>
            </div>
            <Separator />
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div>
                <div class="font-medium">
                  {{ (variant.openCount / variant.sentCount * 100).toFixed(1) }}%
                </div>
                <p class="text-xs text-muted-foreground">Opens</p>
              </div>
              <div>
                <div class="font-medium">
                  {{ (variant.clickCount / variant.sentCount * 100).toFixed(1) }}%
                </div>
                <p class="text-xs text-muted-foreground">Clicks</p>
              </div>
              <div>
                <div class="font-medium">
                  ${{ variant.revenueGenerated.toFixed(2) }}
                </div>
                <p class="text-xs text-muted-foreground">Revenue</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Conversion Rate Comparison -->
      <Card>
        <CardHeader>
          <CardTitle>Conversion Rate Comparison</CardTitle>
          <CardDescription>
            Compare key metrics between variants
          </CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="bar"
            height="350"
            :options="conversionChartOptions"
            :series="conversionChartSeries"
          />
        </CardContent>
      </Card>

      <!-- Time Series Performance -->
      <Card>
        <CardHeader>
          <CardTitle>Performance Over Time</CardTitle>
          <CardDescription>
            Track conversion rates throughout the test period
          </CardDescription>
        </CardHeader>
        <CardContent>
          <apexchart
            type="line"
            height="350"
            :options="timeSeriesOptions"
            :series="timeSeriesSeries"
          />
        </CardContent>
      </Card>

      <!-- Statistical Significance -->
      <Card v-if="selectedTestVariants.length === 2">
        <CardHeader>
          <CardTitle>Statistical Analysis</CardTitle>
          <CardDescription>
            Confidence level and statistical significance of test results
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">Confidence Level</p>
                <p class="text-sm text-muted-foreground">
                  Statistical confidence in test results
                </p>
              </div>
              <Badge :variant="confidenceLevel >= 95 ? 'success' : 'warning'">
                {{ confidenceLevel }}% Confidence
              </Badge>
            </div>
            <Progress :value="confidenceLevel" class="h-2" />
            <p class="text-sm text-muted-foreground">
              {{
                confidenceLevel >= 95
                  ? 'Results are statistically significant'
                  : 'Need more data for statistical significance'
              }}
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
