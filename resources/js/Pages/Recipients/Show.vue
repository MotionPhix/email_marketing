<script setup lang="ts">
import {Link} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";

const {recipient, stats} = defineProps<{
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
    opened: number
    clicked: number
    bounced: number
    complained: number
    delivered: number
    recentInteractions: Array<{
      campaign: {
        uuid: string
        title: string
      }
      status: string // "open", "click", "bounce", etc.
      date: string
    }>
  }
}>()
</script>

<template>
  <AppLayout :title="recipient.name">
    <template #header>
      <PageTitle :title="recipient.name" />
    </template>

    <template #action>

      <div class="flex space-x-2">
        <Link href="/recipients" class="btn-secondary">Back to List</Link>
        <Link :href="`/recipients/${recipient.uuid}/edit`" class="btn-primary">Edit</Link>
      </div>

    </template>

    <div class="py-12">
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

      <!-- Performance Stats Section -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow rounded p-6 text-center">
          <h3 class="text-lg font-semibold">Total Campaigns</h3>
          <p class="text-3xl font-bold">{{ stats.totalCampaigns }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 text-center">
          <h3 class="text-lg font-semibold">Emails Opened</h3>
          <p class="text-3xl font-bold">{{ stats.opened }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 text-center">
          <h3 class="text-lg font-semibold">Links Clicked</h3>
          <p class="text-3xl font-bold">{{ stats.clicked }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 text-center">
          <h3 class="text-lg font-semibold">Bounces</h3>
          <p class="text-3xl font-bold">{{ stats.bounced }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 text-center">
          <h3 class="text-lg font-semibold">Complaints</h3>
          <p class="text-3xl font-bold">{{ stats.complained }}</p>
        </div>

        <div class="bg-white shadow rounded p-6 text-center">
          <h3 class="text-lg font-semibold">Delivered</h3>
          <p class="text-3xl font-bold">{{ stats.delivered }}</p>
        </div>
      </div>

      <!-- Recent Interactions Section -->
      <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Recent Campaign Interactions</h2>
        <ul class="divide-y divide-gray-200">
          <li v-for="interaction in stats.recentInteractions" :key="interaction.campaign.uuid" class="py-3">
            <div class="flex justify-between items-center">
              <Link :href="`/campaigns/${interaction.campaign.uuid}`" class="text-blue-600 hover:underline">
                {{ interaction.campaign.title }}
              </Link>
              <span class="text-sm text-gray-600">{{ interaction.date }}</span>
            </div>
            <span class="text-gray-500">Status: {{ interaction.status }}</span>
          </li>
        </ul>
      </div>
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
