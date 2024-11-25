<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from '@inertiajs/vue3'
import {Button} from "@/Components/ui/button/index.js";
import {ExternalLinkIcon} from "@radix-icons/vue";

defineProps({
  campaigns: Array,
});
</script>

<template>
  <AppLayout title="Campaigns">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Campaigns
      </h2>
    </template>

    <template #action>
      <Button as-child>
        <Link as="button" :href="route('campaigns.create')">
          <span>New</span>
          <ExternalLinkIcon />
        </Link>
      </Button>
    </template>

    <div class="py-12">
      <div v-if="campaigns.length" class="mt-4">
        <table class="w-full table-auto border-collapse border border-gray-300">
          <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-300 px-4 py-2">Title</th>
            <th class="border border-gray-300 px-4 py-2">Status</th>
            <th class="border border-gray-300 px-4 py-2">Scheduled At</th>
            <th class="border border-gray-300 px-4 py-2"></th>
          </tr>
          </thead>

          <tbody>
          <tr v-for="campaign in campaigns" :key="campaign.id">
            <td class="border border-gray-300 px-4 py-2">{{ campaign.title }}</td>
            <td class="border border-gray-300 px-4 py-2 capitalize">{{ campaign.status }}</td>
            <td class="border border-gray-300 px-4 py-2">
              {{ campaign.scheduled_at || 'N/A' }}
            </td>
            <td class="border border-gray-300 px-4 py-2">
              <button
                @click.prevent="$inertia.post(route('campaigns.send', campaign.id))"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Send Now
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <div v-else>

        <h2 class="text-2xl">
          No campaigns
        </h2>

        <p class="text-gray-500">
          You don't have any campaigns yet.
        </p>

        <Link
          as="button"
          :href="route('campaigns.create')">
          Create campaign
        </Link>

      </div>

    </div>

  </AppLayout>
</template>
