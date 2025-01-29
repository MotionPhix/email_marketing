<script setup lang="ts">
import { type Component } from 'vue';

defineProps<{
  title?: string;
  description?: string;
  icon?: Component
}>();
</script>

<template>
  <div class="text-center py-12">
    <div class="flex justify-center" v-if="icon">
      <component :is="icon" class="h-24 w-24 text-gray-400 dark:text-gray-600" />
    </div>

    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
      {{ title || 'No items found' }}
    </h3>

    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      {{ description || 'No items are available at the moment.' }}
    </p>

    <div class="mt-6" v-if="$slots.action">
      <slot name="action"></slot>
    </div>
  </div>
</template>
