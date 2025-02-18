<script setup lang="ts">
import { ref, computed } from 'vue'
import { format, addDays, setHours, setMinutes, isBefore } from 'date-fns'

const props = defineProps<{
  timezone: string
}>()

const emit = defineEmits<{
  (e: 'schedule', date: Date): void
  (e: 'cancel'): void
}>()

const selectedDate = ref<Date>(addDays(new Date(), 1))
const selectedTime = ref('09:00')
const showCustomTime = ref(false)

const timezones = Intl.supportedValuesOf('timeZone')
const selectedTimezone = ref(props.timezone)

// Predefined scheduling options
const quickScheduleOptions = [
  { label: 'Tomorrow at 9 AM', value: addDays(setHours(new Date(), 9), 1) },
  { label: 'Next Monday at 9 AM', value: getNextMonday() },
  { label: 'Next Tuesday at 9 AM', value: getNextTuesday() },
  { label: 'Custom Date & Time', value: 'custom' },
]

const minDate = computed(() => {
  const now = new Date()
  return addDays(now, 1) // Minimum 1 day in advance
})

const scheduleDateTime = computed(() => {
  if (!selectedDate.value) return null

  const [hours, minutes] = selectedTime.value.split(':').map(Number)
  const date = new Date(selectedDate.value)
  return setMinutes(setHours(date, hours), minutes)
})

function getNextMonday(): Date {
  const date = new Date()
  const day = date.getDay()
  const diff = day <= 1 ? 1 - day : 8 - day
  return setHours(addDays(date, diff), 9)
}

function getNextTuesday(): Date {
  const date = new Date()
  const day = date.getDay()
  const diff = day <= 2 ? 2 - day : 9 - day
  return setHours(addDays(date, diff), 9)
}

function handleQuickSchedule(option: { value: Date | 'custom' }) {
  if (option.value === 'custom') {
    showCustomTime.value = true
    return
  }

  emit('schedule', option.value)
}

function handleSchedule() {
  if (!scheduleDateTime.value) return
  if (isBefore(scheduleDateTime.value, new Date())) {
    alert('Please select a future date and time')
    return
  }

  emit('schedule', scheduleDateTime.value)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Quick Schedule Options -->
    <div v-if="!showCustomTime" class="space-y-4">
      <div
        v-for="option in quickScheduleOptions"
        :key="option.label"
        class="flex items-center space-x-2"
      >
        <Button
          variant="outline"
          class="w-full justify-start"
          @click="handleQuickSchedule(option)"
        >
          <Icon
            :name="option.value === 'custom' ? 'calendar' : 'clock'"
            class="mr-2 h-4 w-4"
          />
          {{ option.label }}
        </Button>
      </div>
    </div>

    <!-- Custom Schedule -->
    <div v-else class="space-y-6">
      <!-- Calendar -->
      <div class="space-y-2">
        <Label>Select Date</Label>
        <Calendar
          v-model="selectedDate"
          :min-date="minDate"
          class="rounded-md border"
        />
      </div>

      <!-- Time Selection -->
      <div class="space-y-2">
        <Label>Select Time</Label>
        <Input
          type="time"
          v-model="selectedTime"
          class="w-full"
        />
      </div>

      <!-- Timezone -->
      <div class="space-y-2">
        <Label>Timezone</Label>
        <Select v-model="selectedTimezone">
          <option
            v-for="tz in timezones"
            :key="tz"
            :value="tz"
          >
            {{ tz }}
          </option>
        </Select>
      </div>

      <!-- Schedule Summary -->
      <div
        v-if="scheduleDateTime"
        class="rounded-lg border p-4"
      >
        <h4 class="font-medium">Campaign will be sent:</h4>
        <p class="mt-1 text-sm text-gray-500">
          {{ format(scheduleDateTime, 'MMMM d, yyyy h:mm a') }}
          ({{ selectedTimezone }})
        </p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end space-x-4">
        <Button
          variant="outline"
          @click="showCustomTime = false"
        >
          Back
        </Button>
        <Button
          :disabled="!scheduleDateTime"
          @click="handleSchedule"
        >
          Schedule Campaign
        </Button>
      </div>
    </div>
  </div>
</template>
