<script setup lang="ts">
interface Props {
  classes?: string
}

defineProps<Props>()
</script>

<template>
  <h2
    :class="[
      'text-xl flex items-center gap-x-2 font-semibold leading-tight text-gray-800 truncate dark:text-gray-200',
      classes
    ]">
    <slot />
  </h2>
</template>
