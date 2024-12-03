<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";

defineProps<{
  audience: object
}>()
</script>

<template>

  <AppLayout :title="audience.name">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ audience.name }} Audience
      </h2>
    </template>

    <template #action>
      <Button as-child :href="route('audiences.edit', audience.uuid)">
        <GlobalLink as="button">
          Edit
        </GlobalLink>
      </Button>
    </template>

    <div class="py-12">
      <!-- Audience Overview -->
      <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Overview</h3>
        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ audience.description }}</p>
        <div class="mt-4 flex space-x-4">
          <Button as-child variant="outline" :href="route('audiences.edit', audience.uuid)">
            <span>Edit Audience</span>
          </Button>
          <Button variant="destructive" @click="() => confirmDelete(audience.uuid)">
            Delete Audience
          </Button>
        </div>
      </div>

      <!-- Recipients -->
      <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recipients</h3>
          <Button as-child variant="default" :href="route('recipients.create', audience.uuid)">
            <span>Add Recipient</span>
          </Button>
        </div>

        <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
          <li
            v-for="recipient in audience.recipients"
            :key="recipient.uuid"
            class="py-4 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ recipient.name }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ recipient.email }}</p>
            </div>
            <div class="flex space-x-2">
              <Button as-child size="sm" variant="outline" :href="route('recipients.edit', recipient.uuid)">
                <span>Edit</span>
              </Button>
              <Button size="sm" variant="destructive" @click="() => confirmRemoveRecipient(recipient.uuid)">
                Remove
              </Button>
            </div>
          </li>
        </ul>
      </div>

      <!-- Campaigns -->
      <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Campaigns</h3>
          <Button as-child variant="default" :href="route('campaigns.create', audience.uuid)">
            <span>Create Campaign</span>
          </Button>
        </div>

        <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
          <li
            v-for="campaign in audience.campaigns"
            :key="campaign.uuid"
            class="py-4 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ campaign.title }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ campaign.subject }}</p>
            </div>
            <div class="flex space-x-2">
              <Button as-child size="sm" variant="outline" :href="route('campaigns.edit', campaign.uuid)">
                <span>Edit</span>
              </Button>
              <Button size="sm" variant="destructive" @click="() => confirmDeleteCampaign(campaign.uuid)">
                Delete
              </Button>
            </div>
          </li>
        </ul>
      </div>
    </div>

  </AppLayout>

</template>
