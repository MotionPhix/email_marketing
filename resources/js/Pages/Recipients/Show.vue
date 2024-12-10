<script setup lang="ts">
import {Link} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";
import {onMounted, ref} from "vue";
import {ExternalLinkIcon} from "@radix-icons/vue";
import {Button} from "@/Components/ui/button";

const {recipient, stats, chartData, recentInteractions} = defineProps<{
  recipient: {
    uuid: string
    name: string
    email: string
    audiences?: Array<{
      uuid: string
      name: string
    }>
  },
  stats: {
    totalCampaigns: number
    open: { count: number}
    click: { count: number }
    bounce: { count: number }
    dropped: { count: number }
    delivered: { count: number }
    spamreport: { count: number }
    deferred: { count: number }
    unsubscribe: { count: number }
  },
  recentInteractions: Array<{
    campaign: {
      uuid: string
      title: string
    }
    status: string // "open", "click", "bounce", etc.
    date: string
  }>
  chartData: Record<string, Record<string, number>>
}>()

const chartOptions = ref({
  chart: {
    type: 'line',
    height: 350,
    zoom: false
  },
  xaxis: {
    categories: [] as string[],
  },
  series: [
    {name: 'Opened', data: [] as number[]},
    {name: 'Clicked', data: [] as number[]},
    {name: 'Bounced', data: [] as number[]},
    {name: 'Deferred', data: [] as number[]},
    {name: 'Spam', data: [] as number[]},
  ]
})

const setupChartData = () => {
  const dates = Object.keys(chartData)
  const openedData = dates.map(date => chartData[date]?.open || 0)
  const clickedData = dates.map(date => chartData[date]?.click || 0)
  const bouncedData = dates.map(date => chartData[date]?.bounce || 0)
  const deferredData = dates.map(date => chartData[date]?.deferred || 0)
  const spamData = dates.map(date => chartData[date]?.spamreport || 0)

  chartOptions.value = {
    ...chartOptions.value,
    xaxis: {
      categories: dates
    },
    series: [
      {name: 'Opened', data: openedData},
      {name: 'Clicked', data: clickedData},
      {name: 'Bounced', data: bouncedData},
      {name: 'Deferred', data: deferredData},
      {name: 'Spammed', data: spamData},
    ]
  }
}

onMounted(() => {
  setupChartData()
})
</script>

<template>
  <AppLayout :title="recipient.name">
    <template #header>
      <PageTitle :title="recipient.name"/>
    </template>

    <template #action>

      <div class="flex space-x-2">
        <Button as-child>
          <Link
            as="button"
            class="flex items-center gap-2"
            :href="route('recipients.index')">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M3.99982 11.9998L19.9998 11.9998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"/>
              <path d="M8.99963 17C8.99963 17 3.99968 13.3176 3.99966 12C3.99965 10.6824 8.99966 7 8.99966 7"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="hidden sm:inline-flex">Back</span>
          </Link>
        </Button>

        <Button
          as-child
          variant="outline">
          <GlobalLink
            as="button"
            :href="route('recipients.edit', recipient.uuid)">
            Edit
          </GlobalLink>
        </Button>
      </div>

    </template>

    <div class="py-12 px-6 sm:px-0">
      <!-- Recipient Info Section -->
      <div class="bg-white rounded-lg border p-6 mb-6">
        <ul class="divide-y divide-gray-200">
          <li class="py-3 grid">
            <h2 class="text-2xl font-semibold">
              {{ recipient.name }}
            </h2>
            <span class="text-muted-foreground">{{ recipient.email }}</span>
          </li>

          <li v-if="recipient.audiences?.length" class="py-3">
            <strong class="block font-medium">Audiences</strong>

            <ul class="space-y-2">
              <li v-for="audience in recipient.audiences" :key="audience.uuid">
                <Link :href="`/audiences/${audience.uuid}`" class="text-blue-600 hover:underline">
                  {{ audience.name }}
                </Link>
              </li>
            </ul>
          </li>
        </ul>
      </div>

      <!-- Summary Stats -->
      <section class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 mb-8">

        <div class="bg-white shadow-md rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ stats.open.count }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Open Rate</p>
          <p class="mt-1 text-gray-500">
            Number of emails opened by the recipient over all campaigns.
          </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ stats.click.count }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Click Rate</p>
          <p class="mt-1 text-gray-500">
            Number of links clicked by the recipient over all campaigns.
          </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ stats.bounce.count }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Bounce Rate</p>
          <p class="mt-1 text-gray-500">
            Number of links clicked by the recipient over all campaigns.
          </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ stats.deferred.count }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Deferred Rate</p>
          <p class="mt-1 text-gray-500">
            Number of links clicked by the recipient over all campaigns.
          </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ stats.spamreport.count }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Spam Rate</p>
          <p class="mt-1 text-gray-500">
            Number of links clicked by the recipient over all campaigns.
          </p>
        </div>
      </section>

      <!-- Recent Interactions Section -->
      <div class="bg-white rounded-lg border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">
          Recent Interactions
        </h2>

        <ul class="divide-y divide-gray-200">
          <li
            v-for="(interaction) in recentInteractions"
            :key="interaction.campaign.uuid" class="py-3">
            <Link
              as="button"
              class="grid text-left w-full"
              :href="route('campaigns.show', interaction.campaign.uuid)">
              <div class="font-medium items-center flex gap-1">
                {{ interaction.campaign.title }} <ExternalLinkIcon />
              </div>

              <div class="divide-y mt-4">
                <div
                  class="text-sm flex items-center gap-1 py-1"
                  v-for="event in interaction.campaign.activity">
                  <span class="capitalize text-muted-foreground">
                    {{ event.status }}
                  </span>

                  |

                  <span class="text-muted-foreground">
                    {{ event.date }}
                  </span>
                </div>
              </div>
            </Link>

          </li>
        </ul>
      </div>

      <!-- Chart -->
      <section class="p-6">
        <h2 class="text-lg font-thin mb-4">
          Recipient Performance
          <span class="text-muted-foreground">
            <i>(In campaigns)</i>
          </span>
        </h2>

        <apexchart
          type="line"
          height="350"
          :options="chartOptions"
          :series="chartOptions.series"/>
      </section>
    </div>
  </AppLayout>
</template>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700;
}

.btn-secondary {
  @apply bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700;
}
</style>
