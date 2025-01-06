<script setup lang="ts">
import {Button} from '@/Components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuShortcut,
  DropdownMenuTrigger
} from '@/Components/ui/dropdown-menu'
import {MoonIcon, SunIcon} from "@radix-icons/vue";
import {useColorMode} from '@vueuse/core'

withDefaults(
  defineProps<{
    isRounded?: boolean
    size?: string
  }>(),
  {
    isRounded: false,
    size: '1.2rem'
  }
)

// Pass { disableTransition: false } to enable transitions
const mode = useColorMode()
</script>

<template>
  <DropdownMenu :modal="false">
    <DropdownMenuTrigger as-child>
      <Button
        variant="outline"
        :class="{ 'rounded-full': isRounded }"
        class="dark:text-gray-100" size="icon">
        <MoonIcon :class="`h-[${size}] w-[${size}] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0`"/>
        <SunIcon :class="`absolute h-[${size}] w-[${size}] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100`"/>
        <span class="sr-only">Toggle theme</span>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent align="end">

      <DropdownMenuItem @click="mode = 'light'">
        Light
        <DropdownMenuShortcut v-if="mode === 'light'">
          <SunIcon/>
        </DropdownMenuShortcut>
      </DropdownMenuItem>

      <DropdownMenuItem @click="mode = 'dark'">
        Dark
        <DropdownMenuShortcut v-if="mode === 'dark'">
          <MoonIcon/>
        </DropdownMenuShortcut>
      </DropdownMenuItem>

      <DropdownMenuItem @click="mode = 'auto'">
        System
      </DropdownMenuItem>

    </DropdownMenuContent>
  </DropdownMenu>
</template>
