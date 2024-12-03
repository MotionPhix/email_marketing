<script setup lang="ts">
import { reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import { Button } from "@/Components/ui/button";
import {AvatarImage} from "radix-vue";

const { audiences } = defineProps<{
  audiences: object
}>();
</script>

<template>
  <AppLayout title="Audiences">
    <!-- Header -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Audiences
      </h2>
    </template>

    <!-- Action Button -->
    <template #action>
      <Button class="max-w-md" as-child :href="route('audiences.create')">
        <GlobalLink as="button">
          New Audience
        </GlobalLink>
      </Button>
    </template>

    <!-- Content -->
    <div class="py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="audience in audiences.data"
          :key="audience.id"
          class="flex flex-col border border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
        >
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
            {{ audience.name }}
          </h2>

          <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
            {{ audience.description || 'No description available.' }}
          </p>

          <ul class="mt-4 space-y-2">
            <li
              v-for="recipient in audience.recipients"
              :key="recipient.id"
              class="flex items-center justify-between border-b border-gray-200 dark:border-gray-600 pb-2"
            >
              <div>
                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                  {{ recipient.name }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ recipient.email }}
                </p>
              </div>
              <button
                class="text-blue-500 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-400 text-xs"
                @click="() => alert(`Email: ${recipient.email}`)"
              >
                Contact
              </button>
            </li>
          </ul>

          <div class="flex-1"></div>

          <div class="mt-4 flex justify-between items-center">
            <span
              class="text-xs text-gray-500 dark:text-gray-400"
            >
              <AvatarImage /> +{{ audience.remaining_recipients_count }} recipients
            </span>
            <Button size="sm" as-child :href="route('audiences.edit', audience.id)">
              <GlobalLink as="button">Edit</GlobalLink>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
