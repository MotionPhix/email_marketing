<script setup lang="ts">
import {Link} from "@inertiajs/vue3"
import {useDark} from "@vueuse/core"

const isDark = useDark()
</script>

<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
    <div v-if="$slots.logo">
      <Link :href="route('home')" as="button">
        <slot name="logo"/>
      </Link>
    </div>

    <div class="w-full sm:max-w-md mt-6 overflow-hidden sm:rounded-lg">
      <slot/>
    </div>
  </div>
</template>
