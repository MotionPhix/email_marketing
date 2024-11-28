<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Head, Link, router} from '@inertiajs/vue3';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from '@/Components/ui/card'
import {
  Activity,
  PencilIcon,
  TrashIcon,
  CreditCard,
  DollarSign,
  Users
} from 'lucide-vue-next'
import {computed} from 'vue';
import {Button} from '@/Components/ui/button'

const {campaign, statistics} = defineProps({
  campaign: {
    type: Object,
    required: true,
  },
  statistics: {
    type: Object,
    required: true,
  },
});

const displayedRecipients = computed(() =>
  campaign.audience.recipients.slice(0, 5)
);

// Calculate remaining recipients
const remainingRecipients = computed(() =>
  Math.max(0, campaign.audience.recipients.length - 5)
)

const deleteRecipient = (recipient) => {
  router.delete(route('audiences.remove_recipient', {
    audience: campaign.audience.uuid,
    recipient: recipient.uuid
  }), {
    preserveScroll: true,
  })
};

const chartData = {
  categories: statistics.map((stat) => stat.date),
  series: [
    {name: 'Opens', data: statistics.map((stat) => stat.stats[0]?.metrics.opens || 0)},
    {name: 'Clicks', data: statistics.map((stat) => stat.stats[0]?.metrics.clicks || 0)},
    {name: 'Bounces', data: statistics.map((stat) => stat.stats[0]?.metrics.bounces || 0)},
    {name: 'Spam Reports', data: statistics.map((stat) => stat.stats[0]?.metrics.spam_reports || 0)},
  ],
};

const options = {
  chart: {
    type: 'line',
    height: 300,
  },
  xaxis: {
    categories: chartData.categories,
  },
  stroke: {
    width: 3.5,
    curve: 'smooth'
  },
};

const series = chartData.series

// Helper to aggregate a specific metric across all dates and stats
const getTotalMetric = (metric) => {
  return statistics.reduce((sum, stat) => {
    return (
      sum +
      stat.stats.reduce((innerSum, s) => innerSum + (s.metrics[metric] || 0), 0)
    );
  }, 0);
};

// Compute totals
const totalOpens = computed(() => getTotalMetric('opens'));
const totalClicks = computed(() => getTotalMetric('clicks'));
const totalUniqueClicks = computed(() => getTotalMetric('unique_clicks'));
const totalBounces = computed(() => getTotalMetric('bounces'));
const totalDelivered = computed(() => getTotalMetric('delivered'));
const totalSpams = computed(() => getTotalMetric('spam_reports'));
</script>

<template>

  <Head>
    <title>
      Manage campaign
    </title>
  </Head>

  <AppLayout :title="`${campaign.uuid ? 'Edit' : 'Create'} campaign`">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ campaign.title }}
      </h2>
    </template>

    <template #action>

      <Button
        as-child
        as="button"
        max-width="md"
        :href="route('campaigns.schedule', campaign.uuid)">
        <GlobalLink>
          Schedule
        </GlobalLink>
      </Button>

    </template>

    <div class="p-6">
      <div v-if="!campaign"
           class="text-center">
        <p>Loading campaign details...</p>
      </div>

      <div v-else>
        <!-- Campaign Header -->
        <div class="mb-6 capitalize">

          <h1 class="text-2xl font-bold">{{ campaign.title }}</h1>

          <p class="text-gray-500">{{ campaign.subject }}</p>

          <p class="text-sm text-gray-400 border-t py-2">
            {{ campaign.status }}
          </p>

        </div>

        <!-- Campaign Details -->
        <div class="bg-white shadow-sm p-6 rounded-lg">

          <p class="grid">

            <strong>
              Description
            </strong>

            <span>
              {{ campaign.description || 'No description provided.' }}
            </span>

          </p>

          <p class="grid">
            <strong>Template</strong>
            <span>
              {{ campaign.template?.name || 'No template assigned.' }}
            </span>
          </p>

          <p class="grid">
            <strong>Audience</strong>
            <span>
              {{ campaign.audience?.name || 'No audience assigned.' }}
            </span>
          </p>

          <p class="grid">
            <strong>Scheduled</strong>
            <span>
              {{ campaign.scheduled_at || 'Not scheduled.' }}
            </span>
          </p>

        </div>

        <!-- Recipients List -->
        <div
          v-if="campaign.audience.recipients && campaign.audience.recipients.length"
          class="mt-6 px-6">

          <h2 class="text-lg font-semibold mb-4">Recipients</h2>

          <div
            v-for="recipient in displayedRecipients" :key="recipient.uuid"
            class="group grid grid-cols-[25px_minmax(0,1fr)] items-start last:mb-0 last:pb-0">

            <span class="flex size-3 translate-y-6 rounded-full bg-sky-500"/>

            <div class="py-4">

              <section class="font-medium leading-none flex items-center gap-4">

                <h3 class="py-2">
                  {{ recipient.name }}
                </h3>

                <!-- Quick Edit Actions -->
                <div class="gap-2 hidden group-hover:flex">
                  <Button
                    max-width="sm"
                    :close-button="false"
                    padding-classes="p-0"
                    :close-explicitly="true"
                    class="p-1 w-[1.5rem] h-[1.5rem]"
                    size="icon" as-child>
                    <GlobalLink
                      as="button"
                      :data="{ modal: true }"
                      :href="route('recipients.edit', recipient.uuid)">
                      <PencilIcon />
                    </GlobalLink>
                  </Button>

                  <Button
                    class="p-1 w-[1.5rem] h-[1.5rem]"
                    @click="deleteRecipient(recipient)"
                    size="icon">
                    <TrashIcon />
                  </Button>
                </div>

              </section>

              <p class="text-sm text-muted-foreground">
                {{ recipient.email }}
              </p>

            </div>

          </div>

        </div>

        <div
          v-else
          class="mt-6 text-gray-500 p-6">

          <p>No recipients assigned to this campaign.</p>

        </div>

      </div>

    </div>

    <div class="p-6 rounded-lg bg-zinc-100 my-12">

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Opens
            </CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ totalOpens }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Clicks
            </CardTitle>
            <Users class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              {{ totalClicks }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Bounces
            </CardTitle>
            <CreditCard class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ totalBounces }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Delivered
            </CardTitle>
            <Activity class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ totalDelivered }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Spam Reports
            </CardTitle>
            <Activity class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ totalSpams }}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Unique Clicks
            </CardTitle>
            <Activity class="h-4 w-4 text-muted-foreground"/>
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              {{ totalUniqueClicks }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Chart Section -->
      <div class="mt-12">
        <h2 class="text-xl font-bold">Statistics Overview</h2>
        <apexchart width="100%" height="350" type="line" :options="options" :series="series"/>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
p {
  margin-bottom: 0.5rem;
}
</style>
