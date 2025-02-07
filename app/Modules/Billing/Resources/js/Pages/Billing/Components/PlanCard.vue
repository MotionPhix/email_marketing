<script setup lang="ts">
import { type Plan } from '../../../types'

const props = defineProps<{
  plan: Plan
  isCurrent: boolean
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'select', plan: Plan): void
}>()
</script>

<template>
  <div
    class="relative rounded-lg border p-6 shadow-sm"
    :class="{
      'border-primary-500 ring-2 ring-primary-500': isCurrent,
      'border-gray-200': !isCurrent
    }"
  >
    <!-- Current Plan Badge -->
    <div
      v-if="isCurrent"
      class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary-500 px-3 py-1 text-xs font-medium text-white"
    >
      Current Plan
    </div>

    <!-- Plan Details -->
    <div class="text-center">
      <h3 class="text-lg font-medium text-gray-900">
        {{ plan.name }}
      </h3>

      <div class="mt-4">
        <span class="text-4xl font-bold tracking-tight text-gray-900">
          {{ plan.formattedPrice }}
        </span>
        <span class="text-base font-medium text-gray-500">/month</span>
      </div>

      <!-- Features List -->
      <ul class="mt-6 space-y-4 text-sm text-gray-600">
        <li class="flex items-center">
          <CheckIcon class="h-5 w-5 text-primary-500" />
          <span class="ml-3">{{ plan.features.campaign_limit }}</span>
        </li>
        <li class="flex items-center">
          <CheckIcon class="h-5 w-5 text-primary-500" />
          <span class="ml-3">{{ plan.features.email_limit }}</span>
        </li>
        <li class="flex items-center">
          <CheckIcon class="h-5 w-5 text-primary-500" />
          <span class="ml-3">{{ plan.features.recipient_limit }}</span>
        </li>
        <li class="flex items-center">
          <CheckIcon
            v-if="plan.features.can_schedule_campaigns"
            class="h-5 w-5 text-primary-500"
          />
          <XMarkIcon v-else class="h-5 w-5 text-gray-400" />
          <span class="ml-3">Schedule campaigns</span>
        </li>
        <li class="flex items-center">
          <CheckIcon
            v-if="plan.features.personalisation"
            class="h-5 w-5 text-primary-500"
          />
          <XMarkIcon v-else class="h-5 w-5 text-gray-400" />
          <span class="ml-3">Personalization features</span>
        </li>
      </ul>

      <!-- Action Button -->
      <button
        type="button"
        class="mt-8 w-full rounded-md px-3 py-2 text-sm font-semibold shadow-sm"
        :class="[
          isCurrent
            ? 'bg-primary-50 text-primary-600 hover:bg-primary-100'
            : 'bg-primary-600 text-white hover:bg-primary-500'
        ]"
        :disabled="disabled"
        @click="emit('select', plan)"
      >
        {{ isCurrent ? 'Current Plan' : 'Subscribe' }}
      </button>
    </div>
  </div>
</template>
