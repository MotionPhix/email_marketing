<script setup lang="ts">
import {Link} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";
import {onMounted, ref} from "vue";
import {ArrowLeftIcon, ExternalLinkIcon} from "@radix-icons/vue";
import {Button} from "@/Components/ui/button";
import BackButton from "@/Components/BackButton.vue";
import CustomBadge from "@/Components/CustomBadge.vue";

const {recipient, stats, chartData, recentInteractions, totalEmailsSent} = defineProps<{
  totalEmailsSent: number,
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
    open: {
      count: number
      percentage: number
    }
    click: {
      count: number
      percentage: number
    }
    bounce: {
      count: number
      percentage: number
    }
    dropped: {
      count: number
      percentage: number
    }
    delivered: {
      count: number
      percentage: number
    }
    spamreport: {
      count: number
      percentage: number
    }
    deferred: { count: number }
    unsubscribe: { count: number }
    unique_opens: {
      count: number
      percentage: number
    }
    unique_clicks: {
      count: number
      percentage: number
    }
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

      <div class="flex gap-4 items-center">

        <BackButton />

        <PageTitle :title="recipient.name"/>

      </div>

    </template>

    <template #action>

      <div class="flex space-x-2">

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
      <div class="px-4 sm:p-6 mb-6">
        <ul>
          <li class="grid pb-6">
            <h2 class="text-4xl sm:text-5xl font-thin">
              {{ recipient.name }}
            </h2>
            <span class="text-muted-foreground">{{ recipient.email }}</span>
          </li>

          <li v-if="recipient.audiences?.length" class="grid py-4 gap-4">
            <h3 class="text-xl font-thin grid sm:flex sm:items-center gap-2">
              <span>Audiences</span>

              <span class="text-sm text-muted-foreground">
                 — List groups this recipient is a part of
              </span>
            </h3>

            <ul class="space-y-4 grid max-w-sm">

              <li
                v-for="audience in recipient.audiences"
                class="inline-flex min-w-80 items-center gap-x-2 py-3 px-4 text-sm font-medium bg-white border border-gray-200 text-gray-800 -mt-px first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                :key="audience.uuid">

                <div class="flex justify-between w-full">
                  <Link
                    as="button"
                    :href="route('audiences.show', audience.uuid)"
                    class="w-full text-left">
                    {{ audience.name }}
                  </Link>

                  <span
                    class="inline-flex items-center py-1 px-2 rounded-full text-xs font-medium bg-blue-500 text-white">
                    New
                  </span>
                </div>

              </li>

            </ul>

          </li>
        </ul>
      </div>

      <!-- Summary Stats -->
      <section class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 mb-8">

        <div class="bg-white border rounded-lg p-6">
          <h2 class="text-4xl flex gap-4 items-center font-bold text-indigo-600">
            <span>{{ stats.open.count }}</span>
            <CustomBadge :percentage="stats.open.percentage" type="open" />
          </h2>

          <p class="mt-2 flex items-center justify-between gap-2 text-lg font-medium text-gray-800">
            <span>Open Rate</span>

            <span class="text-xs">
              Unique Opens | {{ stats.unique_opens.count }}
            </span>
          </p>

          <p class="mt-2 pt-2 text-muted-foreground text-sm border-t">
            Count of emails opened by the recipient over all campaigns.
          </p>
        </div>

        <div class="bg-white border rounded-lg p-6">
          <h2 class="text-4xl flex items-center gap-4 font-bold text-indigo-600">
            {{ stats.click.count }} <CustomBadge :percentage="stats.click.percentage" type="click" />
          </h2>

          <p class="mt-2 flex items-center justify-between gap-2 text-lg font-medium text-gray-800">
            <span>Click Rate</span>

            <span class="text-xs">
              Unique Clicks | {{ stats.unique_clicks.count }}
            </span>
          </p>

          <p class="mt-2 pt-2 text-muted-foreground text-sm border-t">
            Count of clicks this recipient made on a link within an email.
          </p>
        </div>

        <div class="bg-white border rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600 flex gap-4 items-center">
            {{ stats.bounce.count }} <CustomBadge :percentage="stats.bounce.percentage" type="bounce" />
          </h2>

          <p class="mt-2 text-lg font-medium text-gray-800">Bounce Rate</p>
          <p class="mt-1 text-muted-foreground text-sm">
            Count of emails denied by this recipient's server permanently.
          </p>
        </div>

        <div class="bg-white border rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600 items-center flex gap-4">
            {{ stats.delivered.count }} <CustomBadge :percentage="stats.delivered.percentage" type="delivered" />
          </h2>

          <p class="mt-2 text-lg font-medium text-gray-800">Delivery Rate</p>
          <p class="mt-1 text-muted-foreground text-sm">
            Number of successful emails delivered to the recipient.
          </p>
        </div>

        <div class="bg-white border rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ stats.spamreport.count }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Spam Rate</p>
          <p class="mt-1 text-muted-foreground text-sm">
            Count of email this recipient marked as spam.
          </p>
        </div>

        <div class="bg-white border rounded-lg p-6">
          <h2 class="text-4xl font-bold text-indigo-600">{{ totalEmailsSent }}</h2>
          <p class="mt-2 text-lg font-medium text-gray-800">Total Sent Emails</p>
          <p class="mt-1 text-muted-foreground text-sm">
            Count of emails sent to the recipient over all campaigns.
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
