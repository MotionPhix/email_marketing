<script setup lang="ts">
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {router} from '@inertiajs/vue3'
import {onMounted, ref, watch} from 'vue'
import {toast} from 'vue-sonner'
import {format} from 'date-fns'
import {useDark} from "@vueuse/core";

interface Campaign {
  id: number
  uuid: string
  title: string
  scheduled_at: Date | null
  frequency: FrequencyType | null
  end_date: Date | null
}

type FrequencyType = 'daily' | 'weekly' | 'bi_weekly' | 'monthly' | 'quarterly'

interface FrequencyOption {
  value: FrequencyType
  label: string
  duration: number
}

const props = defineProps<{
  campaign: Campaign
}>()

const isDark = useDark()
const scheduleCampaignRef = ref()
const scheduledDate = ref<Date | null>(null)
const frequency = ref<FrequencyType>('daily')
const endDate = ref<Date | null>(null)
const isSubmitting = ref(false)

const frequencies: FrequencyOption[] = [
  {value: 'daily', label: 'Daily', duration: 30},
  {value: 'weekly', label: 'Weekly', duration: 90},
  {value: 'bi_weekly', label: 'Fortnight', duration: 120},
  {value: 'monthly', label: 'Monthly', duration: 180},
  {value: 'quarterly', label: 'Quarterly', duration: 360},
]

// Helper function to ensure valid Date objects
const ensureValidDate = (date: Date | string | null): Date | null => {
  if (!date) return null
  try {
    const parsedDate = typeof date === 'string' ? parseISO(date) : date
    return isNaN(parsedDate.getTime()) ? null : parsedDate
  } catch {
    return null
  }
}

// Calculate end date based on frequency
const calculateEndDate = (startDate: Date, selectedFrequency: FrequencyType): Date | null => {
  const frequency = frequencies.find(f => f.value === selectedFrequency)
  if (!frequency) return null

  const validStartDate = ensureValidDate(startDate)
  if (!validStartDate) return null

  return new Date(validStartDate.getTime() + frequency.duration * 24 * 60 * 60 * 1000)
}

// Watch for frequency changes to update end date
watch(frequency, (newFrequency) => {
  if (scheduledDate.value) {
    endDate.value = calculateEndDate(scheduledDate.value, newFrequency)
  }
})

// Watch for scheduled date changes
watch(scheduledDate, (newDate) => {
  if (newDate && frequency.value) {
    endDate.value = calculateEndDate(newDate, frequency.value)
  }
})

const formatDateForAPI = (date: Date | null): string | null => {
  if (!date) return null
  return format(date, 'yyyy-MM-dd HH:mm:ss')
}

const handleSchedule = async () => {
  if (!scheduledDate.value) {
    toast.error('Please select a start date')
    return
  }

  isSubmitting.value = true

  try {
    await router.put(route('campaigns.console', props.campaign.uuid), {
      scheduled_at: formatDateForAPI(scheduledDate.value),
      frequency: frequency.value,
      end_date: formatDateForAPI(endDate.value)
    }, {
      preserveScroll: true,
      onSuccess: () => {
        scheduleCampaignRef.value.onClose()
        toast.success('Campaign scheduled successfully', {
          description: `Campaign will start sending on ${format(scheduledDate.value!, 'PPP')}`
        })
      },
      onError: (errors) => {
        toast.error('Failed to schedule campaign', {
          description: Object.values(errors).join('\n')
        })
      }
    })
  } finally {
    isSubmitting.value = false
  }
}

// Initialize with existing schedule if available
onMounted(() => {
  if (props.campaign.scheduled_at) {
    scheduledDate.value = ensureValidDate(props.campaign.scheduled_at)
    frequency.value = props.campaign.frequency as FrequencyType || 'daily'
    endDate.value = props.campaign.end_date ? ensureValidDate(props.campaign.end_date) : null
  }
})
</script>

<template>
  <GlobalModal ref="scheduleCampaignRef" padding="0">
    <CardHeader>
      <CardTitle>Schedule Campaign</CardTitle>
      <CardDescription>
        Set when you want to send <strong>"{{ campaign.title }}"</strong>
      </CardDescription>
    </CardHeader>

    <CardContent>
      <div class="grid gap-4 py-4">
        <!-- Frequency Select -->
        <div class="grid gap-2">
          <label class="text-sm font-medium">Frequency</label>
          <Select v-model="frequency">
            <SelectTrigger>
              <SelectValue placeholder="Select frequency"/>
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="option in frequencies"
                :key="option.value"
                :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Start Date Picker -->
        <div class="grid gap-2">
          <label class="text-sm font-medium">Start Date</label>
          <VDatePicker
            @dayclick="(_, event) => event.target.blur()"
            v-model="scheduledDate"
            mode="dateTime"
            expanded
            :min-date="new Date()"
            :is-dark="isDark"
            :model-config="{
              type: 'ts',
              timezone: 'UTC'
            }"
          />
        </div>

        <!-- End Date Picker (for recurring campaigns) -->
        <div v-if="frequency !== 'once'" class="grid gap-2">
          <label class="text-sm font-medium">
            End Date
            <span class="text-xs text-muted-foreground">
              (Automatically calculated, but can be modified)
            </span>
          </label>
          <VDatePicker
            v-model="endDate"
            mode="dateTime"
            @dayclick="(_, event) => event.target.blur()"
            :min-date="scheduledDate || new Date()"
            :is-dark="isDark"
            :model-config="{
              type: 'ts',
              timezone: 'UTC'
            }">
            <template #default="{ inputValue, inputEvents }">
              <input
                :value="inputValue"
                v-on="inputEvents"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :placeholder="`Ends ${frequency === 'daily' ? 'in 30 days' : frequency === 'weekly' ? 'in 90 days' : 'in 180 days'}`"
              />
            </template>
          </VDatePicker>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3">
        <Button
          type="button"
          variant="outline"
          @click="scheduleCampaignRef.onClose()">
          Cancel
        </Button>

        <Button
          type="button"
          :disabled="!scheduledDate || isSubmitting"
          :loading="isSubmitting"
          @click="handleSchedule">
          Schedule Campaign
        </Button>
      </div>
    </CardContent>
  </GlobalModal>
</template>
