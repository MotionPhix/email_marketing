<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next'
import { useDark, useToggle } from '@vueuse/core'

const isDark = useDark({
  // Target selector
  selector: 'html',
  // Class name that applies dark theme
  class: 'dark',
  // Storage key
  storageKey: 'theme',
  // Value applied when dark mode is enabled
  valueDark: 'dark',
  // Value applied when dark mode is disabled
  valueLight: 'light',
})
const toggleDark = useToggle(isDark)
</script>

<template>
  <Button
    variant="ghost"
    size="icon"
    @click="toggleDark()"
    class="h-9 w-9">
    <Sun
      v-if="isDark"
      class="h-[1.2rem] w-[1.2rem] transition-all"
    />
    <Moon
      v-else
      class="h-[1.2rem] w-[1.2rem] transition-all"
    />
    <span class="sr-only">Toggle theme</span>
  </Button>
</template>
