<script setup lang="ts">
import RecipientIcon from '@/Components/icon/RecipientIcon.vue';

defineProps<{
  title: string
  description?: string
}>()
</script>

<template>
  <div class="flex flex-col items-center gap-3">
    <RecipientIcon class="w-48 h-48 text-gray-400" stroke-width="1" />

    <h2 class="mt-6 text-xl font-semibold leading-none text-center text-gray-500">
      No {{ title }} found!
    </h2>

    <p class="text-sm text-center text-gray-500">
      {{ description }}
    </p>

    <slot />
  </div>
</template>
