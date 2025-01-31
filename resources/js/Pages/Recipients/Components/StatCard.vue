<script setup lang="ts">
import {type Component, computed} from 'vue'
import { cn } from '@/lib/utils'

interface StatCardProps {
  title: string
  value: number
  icon?: Component
  className?: string
  trend?: {
    value: number
    direction: 'up' | 'down' | 'neutral'
  }
}

const props = defineProps<StatCardProps>()

const trendColor = computed(() => {
  if (!props.trend) return ''

  switch (props.trend.direction) {
    case 'up':
      return 'text-green-600 dark:text-green-400'
    case 'down':
      return 'text-red-600 dark:text-red-400'
    default:
      return 'text-gray-600 dark:text-gray-400'
  }
})
</script>

<template>
  <div
    :class="cn(
      'relative overflow-hidden rounded-lg border bg-background p-2',
      'transition-all duration-200 hover:border-foreground/20',
      'dark:bg-gray-800/50 dark:border-gray-700/50',
      className
    )">
    <div class="flex flex-col gap-2">

      <component
        :is="icon"
        v-if="icon"
        class="size-6 text-accent shrink-0"
      />

      <div class="space-y-2 grid">
        <p class="text-sm font-medium text-muted-foreground">
          {{ title }}
        </p>

        <div>
          <h3 class="text-2xl font-bold tracking-tight">
            {{ value.toLocaleString() }}
          </h3>

          <div v-if="trend" :class="['text-xs font-medium', trendColor]">
            {{ trend.value }}%
          </div>
        </div>
      </div>
    </div>

    <div
      class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent"
      :class="className"
    />
  </div>
</template>

<style scoped>
.bg-gradient-overlay {
  background: linear-gradient(
    to right,
    transparent,
    var(--gradient-color, hsl(var(--primary))),
    transparent
  );
}
</style>
