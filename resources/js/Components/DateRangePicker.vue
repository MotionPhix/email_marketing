<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue'
import { DatePicker } from 'v-calendar'
import { Button } from '@/Components/ui/button'
import { CalendarIcon } from 'lucide-vue-next'
import { format } from 'date-fns'
import 'v-calendar/style.css'

interface DateRange {
  start: Date | null
  end: Date | null
}

interface Props {
  modelValue: DateRange
  placeholder?: string
  disabled?: boolean
  minDate?: Date
  maxDate?: Date
}

interface Emits {
  (e: 'update:modelValue', value: DateRange): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select date range',
  disabled: false,
  minDate: undefined,
  maxDate: undefined
})

const emit = defineEmits<Emits>()

// State
const isOpen = ref(false)
const localValue = ref<DateRange>({
  start: props.modelValue?.start ?? null,
  end: props.modelValue?.end ?? null
})

// Time rules to ensure consistent time handling
const rules = [
  {
    hours: 0,
    minutes: 0,
    seconds: 0,
    milliseconds: 0,
  },
  {
    hours: 23,
    minutes: 59,
    seconds: 59,
    milliseconds: 999,
  },
]

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  localValue.value = {
    start: newValue?.start ?? null,
    end: newValue?.end ?? null
  }
}, { deep: true })

// Format the date range for display
const formatDateRange = (range: DateRange | null): string => {
  if (!range?.start || !range?.end) {
    return props.placeholder
  }

  try {
    return `${format(range.start, 'PP')} - ${format(range.end, 'PP')}`
  } catch (error) {
    console.error('Error formatting date range:', error)
    return props.placeholder
  }
}

// Handle applying the selected date range
const handleApply = () => {
  if (localValue.value.start && localValue.value.end) {
    emit('update:modelValue', {
      start: new Date(localValue.value.start),
      end: new Date(localValue.value.end)
    })
    isOpen.value = false
  }
}

// Handle clicking outside the date picker
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.date-range-picker')) {
    isOpen.value = false
  }
}

// Handle resetting the selection
const handleReset = () => {
  localValue.value = {
    start: null,
    end: null
  }
}

// Manage click outside listener
watch(isOpen, (newValue) => {
  if (newValue) {
    document.addEventListener('click', handleClickOutside)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

// Cleanup
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="date-range-picker relative">
    <Button
      type="button"
      variant="outline"
      :disabled="disabled"
      @click="isOpen = !isOpen">
      <CalendarIcon class="mr-2 h-4 w-4" />
      {{ formatDateRange(localValue) }}
    </Button>

    <div
      v-if="isOpen"
      class="absolute right-0 z-50 mt-2 rounded-md border bg-popover shadow-md">
      <DatePicker
        v-model.range="localValue"
        mode="range"
        :min-date="minDate"
        :max-date="maxDate"
        :masks="{
          input: 'YYYY-MM-DD'
        }"
        :attributes="[
          {
            key: 'range',
            highlight: true,
            dates: localValue
          }
        ]"
        :rules="rules"
        trim-weeks
        is-expanded>
        <template #footer>
          <div class="flex items-center justify-between gap-2 border-t p-3">
            <Button
              variant="ghost"
              size="sm"
              @click="handleReset">
              Reset
            </Button>
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                @click="isOpen = false">
                Cancel
              </Button>
              <Button
                size="sm"
                :disabled="!localValue.start || !localValue.end"
                @click="handleApply">
                Apply
              </Button>
            </div>
          </div>
        </template>
      </DatePicker>
    </div>
  </div>
</template>

<style scoped>
.vc-container {
  --vc-font-family: theme('fontFamily.sans');
  --vc-rounded-full: theme('borderRadius.full');
  --vc-rounded: theme('borderRadius.DEFAULT');

  /* Theme colors */
  --vc-gray-50: hsl(var(--background));
  --vc-gray-100: hsl(var(--muted));
  --vc-gray-200: hsl(var(--border));
  --vc-gray-300: hsl(var(--border));
  --vc-gray-400: hsl(var(--muted-foreground));
  --vc-gray-500: hsl(var(--muted-foreground));
  --vc-gray-600: hsl(var(--foreground));
  --vc-gray-700: hsl(var(--foreground));
  --vc-gray-800: hsl(var(--foreground));
  --vc-gray-900: hsl(var(--foreground));

  /* Primary colors with opacity */
  --vc-primary-50: hsl(var(--primary) / 0.1);
  --vc-primary-100: hsl(var(--primary) / 0.2);
  --vc-primary-200: hsl(var(--primary) / 0.3);
  --vc-primary-300: hsl(var(--primary) / 0.4);
  --vc-primary-400: hsl(var(--primary) / 0.5);
  --vc-primary-500: hsl(var(--primary));
  --vc-primary-600: hsl(var(--primary));
  --vc-primary-700: hsl(var(--primary));
  --vc-primary-800: hsl(var(--primary));
  --vc-primary-900: hsl(var(--primary));
}

.dark .vc-container {
  --vc-bg: hsl(var(--background));
  --vc-border: hsl(var(--border));

  /* Dark mode colors */
  --vc-gray-50: hsl(var(--foreground));
  --vc-gray-100: hsl(var(--foreground));
  --vc-gray-200: hsl(var(--muted-foreground));
  --vc-gray-300: hsl(var(--muted-foreground));
  --vc-gray-400: hsl(var(--muted));
  --vc-gray-500: hsl(var(--muted));
  --vc-gray-600: hsl(var(--background));
  --vc-gray-700: hsl(var(--background));
  --vc-gray-800: hsl(var(--background));
  --vc-gray-900: hsl(var(--background));
}
</style>
