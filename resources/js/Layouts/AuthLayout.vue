<script setup lang="ts">
import { computed } from 'vue'
import { useDark } from '@vueuse/core'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const isDark = useDark()

const logoUrl = computed(() =>
  isDark.value ? '/images/logo-dark.svg' : '/images/logo-light.svg'
)
</script>

<template>
  <div class="container relative min-h-screen flex-col items-center justify-center grid lg:max-w-none lg:grid-cols-2 lg:px-0">
    <div class="relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex">
      <div class="absolute inset-0 bg-primary" />
      <div class="relative z-20 flex items-center text-lg font-medium">
        <ApplicationLogo class="h-8" />
      </div>

      <div class="relative z-20 mt-auto">
        <blockquote class="space-y-2">
          <p class="text-lg">
            "This email marketing platform has completely transformed how we engage with our customers. The analytics and automation features are simply outstanding."
          </p>
          <footer class="text-sm">Sophie Lennon</footer>
        </blockquote>
      </div>
    </div>
    <slot />
  </div>
</template>
