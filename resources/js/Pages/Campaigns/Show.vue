<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle
} from '@/Components/ui/card'
import {
  Activity,
  ArrowUpRight,
  CircleUser,
  CreditCard,
  DollarSign,
  Menu,
  Package2,
  Search,
  Users
} from 'lucide-vue-next'
import { onMounted } from 'vue';

const { campaign, statistics } = defineProps({
  campaign: {
    type: Object,
    required: true,
  },
  statistics: {
    type: Object,
    required: true,
  },
});

const chartData = {
  categories: statistics.map((stat) => stat.date),
  series: [
    { name: 'Opens', data: statistics.map((stat) => stat.metrics.opens) },
    { name: 'Clicks', data: statistics.map((stat) => stat.metrics.clicks) },
    { name: 'Bounces', data: statistics.map((stat) => stat.metrics.bounces) },
    { name: 'Spam Reports', data: statistics.map((stat) => stat.metrics.spam_reports) },
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
};

const series = chartData.series
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

    <div class="p-6">
      <div v-if="!campaign"
           class="text-center">
        <p>Loading campaign details...</p>
      </div>

      <div v-else>
        <!-- Campaign Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold">{{ campaign.title }}</h1>
          <p class="text-gray-500">{{ campaign.subject }}</p>
          <p class="text-sm text-gray-400">
            Created at: {{ new Date(campaign.created_at).toLocaleString() }}
          </p>
        </div>

        <!-- Campaign Details -->
        <div class="bg-white shadow-sm p-6 rounded-lg">
          <h2 class="text-lg font-semibold mb-4">Details</h2>
          <p><strong>Description:</strong> {{ campaign.description || 'No description provided.' }}</p>
          <p><strong>Template:</strong> {{ campaign.template?.name || 'No template assigned.' }}</p>
          <p><strong>Audience:</strong> {{ campaign.audience?.name || 'No audience assigned.' }}</p>
          <p><strong>Scheduled At:</strong> {{ campaign.scheduled_at || 'Not scheduled.' }}</p>
        </div>

        <!-- Recipients List -->
        <div v-if="campaign.recipients && campaign.recipients.length"
             class="mt-6">
          <h2 class="text-lg font-semibold mb-4">Recipients</h2>
          <ul class="list-disc pl-6">
            <li v-for="recipient in campaign.audience.recipients"
                :key="recipient.id">
              {{ recipient.name }} ({{ recipient.email }})
            </li>
          </ul>
        </div>

        <div v-else
             class="mt-6 text-gray-500">
          <p>No recipients assigned to this campaign.</p>
        </div>
      </div>
    </div>

    <div class="p-6 rounded-lg bg-zinc-100 my-12">

      <div>
        <h1 class="text-2xl font-bold">{{ campaign.title }}</h1>
        <p class="text-gray-400">Subject: {{ campaign.subject }}</p>
        <p class="text-gray-400">Status: {{ campaign.status }}</p>
      </div>

      <!-- Statistics Cards -->
<!--      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">-->
<!--        <div class="bg-gray-800 p-4 rounded-lg text-center">-->
<!--          <h3 class="text-lg font-semibold">Opens</h3>-->
<!--          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.opens, 0) }}</p>-->
<!--        </div>-->

<!--        <div class="bg-gray-800 p-4 rounded-lg text-center">-->
<!--          <h3 class="text-lg font-semibold">Clicks</h3>-->
<!--          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.clicks, 0) }}</p>-->
<!--        </div>-->

<!--        <div class="bg-gray-800 p-4 rounded-lg text-center">-->
<!--          <h3 class="text-lg font-semibold">Bounces</h3>-->
<!--          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.bounces, 0) }}</p>-->
<!--        </div>-->
<!--        <div class="bg-gray-800 p-4 rounded-lg text-center">-->
<!--          <h3 class="text-lg font-semibold">Spam Reports</h3>-->
<!--          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.spam_reports, 0) }}</p>-->
<!--        </div>-->
<!--      </div>-->

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Total Revenue
            </CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              $45,231.89
            </div>
            <p class="text-xs text-muted-foreground">
              +20.1% from last month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Subscriptions
            </CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">
              +2350
            </div>
            <p class="text-xs text-muted-foreground">
              +180.1% from last month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Sales
            </CardTitle>
            <CreditCard class="h-4 w-4 text-muted-foreground" />
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              +12,234
            </div>
            <p class="text-xs text-muted-foreground">
              +19% from last month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              Active Now
            </CardTitle>
            <Activity class="h-4 w-4 text-muted-foreground" />
          </CardHeader>

          <CardContent>
            <div class="text-2xl font-bold">
              +573
            </div>
            <p class="text-xs text-muted-foreground">
              +201 since last hour
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Chart Section -->
      <div class="mt-12">
        <h2 class="text-xl font-bold">Statistics Overview</h2>
        <apexchart width="100%" height="300" type="line" :options="options" :series="series" />
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
p {
  margin-bottom: 0.5rem;
}
</style>
