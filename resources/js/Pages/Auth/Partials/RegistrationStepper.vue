<script setup lang="ts">
import {CheckIcon} from "@radix-icons/vue";

const props = defineProps<{
  currentStep: number
  steps: Array<{
    title: string
    description: string
  }>
}>()
</script>

<template>
  <div class="space-y-4">
    <div class="relative">
      <div class="absolute left-0 top-4 h-0.5 w-full bg-muted">
        <div
          class="absolute h-full bg-primary transition-all duration-500"
          :style="{
            width: `${((currentStep - 1) / (steps.length - 1)) * 100}%`
          }"
        />
      </div>
      <div class="relative flex justify-between">
        <div
          v-for="(step, index) in steps"
          :key="index"
          class="flex flex-col items-center">
          <div
            class="flex h-8 w-8 items-center justify-center rounded-full border-2 transition-colors duration-500"
            :class="[
              index + 1 <= currentStep
                ? 'border-primary bg-primary text-primary-foreground'
                : 'border-muted bg-background',
              index + 1 === currentStep && 'animate-pulse'
            ]">
            <CheckIcon
              v-if="index + 1 < currentStep"
              class="h-4 w-4"
            />
            <span v-else>{{ index + 1 }}</span>
          </div>

          <span
            class="mt-2 text-xs font-medium"
            :class="index + 1 <= currentStep ? 'text-foreground' : 'text-muted-foreground'">
            {{ step.title }}
          </span>
        </div>
      </div>
    </div>

    <p class="text-center text-sm text-muted-foreground">
      {{ steps[currentStep - 1].description }}
    </p>
  </div>
</template>
