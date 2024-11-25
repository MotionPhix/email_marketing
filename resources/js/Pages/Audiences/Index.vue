<script setup>
import {reactive} from 'vue';
import {Head} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";

const {audiences} = defineProps({audiences: Array});

const state = reactive({
  audiences,
});
</script>

<template>

  <AppLayout title="Audiences">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Audiences
      </h2>
    </template>

    <div class="py-12 space-y-6">

      <div
        v-for="audience in state.audiences"
        :key="audience.id" class="border rounded-lg p-4">

        <h2 class="text-lg font-semibold">
          {{ audience.name }}
        </h2>

        <p class="text-sm text-gray-500">
          {{ audience.description }}
        </p>

        <ul class="mt-6 divide-y">
          <li
            v-for="recipient in audience.recipients"
            :key="recipient.id" class="py-2">

            <h2 class="font-medium">{{ recipient.name }}</h2>

            <div class="text-sm text-gray-400">
              {{ recipient.email }}
            </div>

          </li>
        </ul>
      </div>

    </div>

  </AppLayout>
</template>
