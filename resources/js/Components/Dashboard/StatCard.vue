<script setup lang="ts">
import {type Component} from "vue";

defineProps<{
  title: string
  value: number
  icon?: Component
}>()
</script>

<template>
  <div class="p-6 rounded-lg border">
    <h3 class="font-serif font-bold text-7xl text-indigo-600 mb-3 text-center lg:text-left">
      {{ value }}
    </h3>

    <p
      class="text-gray-900 text-center lg:text-left">
      {{ title }}
    </p>
  </div>
</template>
