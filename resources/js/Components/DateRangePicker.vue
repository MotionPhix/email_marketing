<script setup lang="ts">
import { ref, watch } from 'vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { useDark } from "@vueuse/core";

interface DateRange {
  start: string | null
  end: string | null
}

const props = defineProps<{
  modelValue: DateRange | null
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: DateRange | null]
}>()

const dates = ref<Date[] | null>(null)
const isDark = useDark()

// Initialize dates from props
watch(() => props.modelValue, (newValue) => {
  if (newValue && newValue.start && newValue.end) {
    dates.value = [new Date(newValue.start), new Date(newValue.end)]
  } else {
    dates.value = null
  }
}, { immediate: true })

const updateDates = (newDates: Date[] | null) => {
  if (!newDates) {
    emit('update:modelValue', null)
    return
  }

  emit('update:modelValue', {
    start: newDates[0].toISOString().split('T')[0],
    end: newDates[1].toISOString().split('T')[0]
  })
}

const presetRanges = [
  {
    label: 'This month',
    range: [
      new Date(new Date().setDate(1)),
      new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)
    ]
  },
  {
    label: 'Last month',
    range: [
      new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1),
      new Date(new Date().getFullYear(), new Date().getMonth(), 0)
    ]
  },
  {
    label: 'Next 30 days',
    range: [
      new Date(),
      new Date(new Date().setDate(new Date().getDate() + 30))
    ]
  }
]
</script>

<template>
  <div class="relative">
    <Datepicker
      v-model="dates"
      range
      :enable-time-picker="false"
      format="yyyy-MM-dd"
      :placeholder="placeholder"
      :clearable="true"
      :preset-ranges="presetRanges"
      auto-apply
      :dark="isDark"
      text-input
      @update:modelValue="updateDates"
      class="date-picker-input">
      <template #trigger>
        <div class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
          <span v-if="!dates" class="text-muted-foreground">
            {{ placeholder || 'Select date range' }}
          </span>
          <span v-else class="text-foreground">
            {{ dates[0].toLocaleDateString() }} - {{ dates[1].toLocaleDateString() }}
          </span>
          <svg
            class="h-4 w-4 opacity-50"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"
            />
          </svg>
        </div>
      </template>
    </Datepicker>
  </div>
</template>

<style>
.date-picker-input {
  width: 100%;
}

.dp__input {
  display: none !important;
}

.dp__main {
  /* Match your UI's font */
  font-family: inherit;
}

.dp__theme_light {
  --dp-background-color: #ffffff;
  --dp-text-color: #374151;
  --dp-hover-color: #f3f4f6;
  --dp-hover-text-color: #111827;
  --dp-hover-icon-color: #111827;
  --dp-primary-color: #2563eb;
  --dp-primary-text-color: #ffffff;
  --dp-secondary-color: #e5e7eb;
  --dp-border-color: #e5e7eb;
  --dp-menu-border-color: #e5e7eb;
  --dp-border-color-hover: #9ca3af;
  --dp-disabled-color: #d1d5db;
  --dp-scroll-bar-background: #f3f4f6;
  --dp-scroll-bar-color: #9ca3af;
  --dp-success-color: #2563eb;
  --dp-success-color-disabled: #d1d5db;
  --dp-icon-color: #6b7280;
  --dp-danger-color: #ef4444;
}

.dp__theme_dark {
  --dp-background-color: #1f2937;
  --dp-text-color: #f3f4f6;
  --dp-hover-color: #374151;
  --dp-hover-text-color: #f3f4f6;
  --dp-hover-icon-color: #f3f4f6;
  --dp-primary-color: #2563eb;
  --dp-primary-text-color: #f3f4f6;
  --dp-secondary-color: #374151;
  --dp-border-color: #374151;
  --dp-menu-border-color: #374151;
  --dp-border-color-hover: #6b7280;
  --dp-disabled-color: #4b5563;
  --dp-scroll-bar-background: #374151;
  --dp-scroll-bar-color: #6b7280;
  --dp-success-color: #2563eb;
  --dp-success-color-disabled: #4b5563;
  --dp-icon-color: #9ca3af;
  --dp-danger-color: #ef4444;
}

.dp__range_end,
.dp__range_start,
.dp__active_date {
  @apply bg-primary text-primary-foreground;
}

.dp__range_between {
  @apply bg-primary/10;
}
</style>
