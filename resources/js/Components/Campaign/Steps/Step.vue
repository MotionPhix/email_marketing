<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Step {
  id: number
  name: string
  description?: string
}

defineProps<{
  steps: Step[]
  currentStep: number
}>()
</script>

<template>
  <nav aria-label="Progress">
    <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0">
      <li v-for="(step, index) in steps" :key="step.id" class="md:flex-1">
        <div
          :class="cn(
            'group flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pl-0 md:pt-4',
            currentStep > step.id
              ? 'border-primary'
              : currentStep === step.id
              ? 'border-primary'
              : 'border-border'
          )">
          <span
            :class="cn(
              'text-sm font-medium',
              currentStep > step.id
                ? 'text-primary'
                : currentStep === step.id
                ? 'text-primary'
                : 'text-muted-foreground'
            )">
            Step {{ index + 1 }}
          </span>
          <span class="text-sm font-medium">{{ step.name }}</span>
          <span
            v-if="step.description"
            :class="cn(
              'text-sm',
              currentStep >= step.id ? 'text-muted-foreground' : 'text-muted-foreground/60'
            )">
            {{ step.description }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>
