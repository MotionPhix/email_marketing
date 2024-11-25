<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

// Props from Inertia
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

// Example: Transform SendGrid statistics for chart rendering
const chartData = {
  categories: statistics.map((stat) => stat.date),
  series: [
    { name: 'Opens', data: statistics.map((stat) => stat.metrics.opens) },
    { name: 'Clicks', data: statistics.map((stat) => stat.metrics.clicks) },
    { name: 'Bounces', data: statistics.map((stat) => stat.metrics.bounces) },
    { name: 'Spam Reports', data: statistics.map((stat) => stat.metrics.spam_reports) },
  ],
};

// Render Chart on Component Mount
onMounted(() => {
  const options = {
    chart: {
      type: 'line',
      height: 300,
    },
    series: chartData.series,
    xaxis: {
      categories: chartData.categories,
    },
  };
  new ApexCharts(document.querySelector('#campaign-chart'), options).render();
});
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

    <div class="p-6 bg-gray-900 text-white min-h-screen">
      <div>
        <h1 class="text-2xl font-bold">{{ campaign.name }}</h1>
        <p class="text-gray-400">Subject: {{ campaign.subject }}</p>
        <p class="text-gray-400">Status: {{ campaign.status }}</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-gray-800 p-4 rounded-lg text-center">
          <h3 class="text-lg font-semibold">Opens</h3>
          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.opens, 0) }}</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg text-center">
          <h3 class="text-lg font-semibold">Clicks</h3>
          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.clicks, 0) }}</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg text-center">
          <h3 class="text-lg font-semibold">Bounces</h3>
          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.bounces, 0) }}</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-lg text-center">
          <h3 class="text-lg font-semibold">Spam Reports</h3>
          <p class="text-2xl">{{ statistics.reduce((sum, stat) => sum + stat.metrics.spam_reports, 0) }}</p>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="mt-6 bg-gray-800 p-6 rounded-lg">
        <h2 class="text-xl font-bold">Statistics Overview</h2>
        <div id="campaign-chart"></div>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
p {
  margin-bottom: 0.5rem;
}
</style>
