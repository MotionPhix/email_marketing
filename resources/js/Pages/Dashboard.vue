<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PageTitle from "@/Components/PageTitle.vue";
import {usePage} from '@inertiajs/vue3'
import Chart from "@/Components/Dashboard/Chart.vue";
import EventFeed from "@/Components/Dashboard/EventFeed.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";

const {props} = usePage()
const {stats, chartData, eventFeed, currentTime} = props
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <PageTitle title="Dashboard"/>
    </template>

    <div class="py-12">

      <div class="p-6 space-y-6">
        <!-- Welcome message and current time -->
        <header class="grid pb-12">
          <span>Welcome</span>
          <h1 class="text-3xl sm:text-4xl font-thin">
            {{ props.auth.user.name }}
          </h1>

          <span class="text-gray-600 text-sm">
            — {{ currentTime }}
          </span>
        </header>

        <!-- Stats overview section -->
        <section class="grid grid-cols-3 gap-6">
          <StatCard title="Total Emails Sent" :value="stats.totalEmailsSent"/>
          <StatCard title="Total Recipients" :value="stats.totalRecipients"/>
          <StatCard title="Total Campaigns" :value="stats.totalCampaigns"/>
        </section>

        <!-- Chart section -->
        <section class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-bold mb-4">Email Performance</h2>
          <Chart :chart-data="chartData"/>
        </section>

        <!-- Event feed section -->
        <section class="bg-white p-6 rounded-lg shadow">
          <h2 class="text-lg font-bold mb-4">Recent Activity</h2>
          <EventFeed :events="eventFeed"/>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
