<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Calendar, Clock, AlertCircle, XCircle } from 'lucide-vue-next'
import { toast } from 'vue-sonner'

interface Props {
  campaign: {
    uuid: string
    status: string
    formatted_scheduled_at: string | null
    formatted_end_date: string | null
    frequency: string | null
  }
}

type FrequencyType = 'daily' | 'weekly' | 'bi_weekly' | 'monthly' | 'quarterly'

interface FrequencyOption {
  value: FrequencyType
  label: string
  duration: number
}

const props = defineProps<Props>()

const frequencies: FrequencyOption[] = [
  {value: 'daily', label: 'Daily', duration: 30},
  {value: 'weekly', label: 'Weekly', duration: 90},
  {value: 'bi_weekly', label: 'Fortnight', duration: 120},
  {value: 'monthly', label: 'Monthly', duration: 180},
  {value: 'quarterly', label: 'Quarterly', duration: 360},
]

const isScheduled = computed(() =>
  props.campaign.status === 'scheduled' && props.campaign.formatted_scheduled_at
)

const scheduleStatus = computed(() => {
  if (!isScheduled.value) return null

  return {
    icon: Clock,
    label: 'Scheduled',
    description: `${props.campaign.formatted_scheduled_at}${
      props.campaign.formatted_end_date
        ? ` until ${props.campaign.formatted_end_date}`
        : ''
    }`,
    frequency: props.campaign.frequency
      ? `Running ${props.campaign.frequency}`
      : 'One-time send'
  }
})

const handleCancelSchedule = async () => {
  try {
    await router.post(
      route('campaigns.cancel_schedule', props.campaign.uuid),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Campaign schedule cancelled successfully')
        },
        onError: (errors) => {
          toast.error('Failed to cancel schedule', {
            description: errors.message || 'An error occurred'
          })
        }
      }
    )
  } catch (error) {
    toast.error('Error cancelling schedule', {
      description: 'Please try again later'
    })
  }
}
</script>

<template>
  <Card class="mt-4">
    <CardContent class="pt-6">
      <!-- Not Scheduled State -->
      <div v-if="!isScheduled" class="flex items-start gap-4">
        <div class="rounded-full bg-muted p-2">
          <Calendar class="h-4 w-4 text-muted-foreground" />
        </div>
        <div class="grid gap-1">
          <h3 class="font-medium">Not Scheduled</h3>
          <p class="text-sm text-muted-foreground">
            This campaign hasn't been scheduled yet. Click the Schedule button above to set up delivery.
          </p>
        </div>
      </div>

      <!-- Scheduled State -->
      <div v-else class="space-y-4">
        <div class="flex items-start gap-4">
          <div class="rounded-full bg-blue-500/20 p-2">
            <Clock class="h-4 w-4 text-blue-700" />
          </div>
          <div class="grid flex-1 gap-1">
            <div class="flex items-center justify-between">
              <h3 class="font-medium">Scheduled</h3>
              <Button
                variant="ghost"
                size="sm"
                class="text-destructive hover:text-destructive hover:bg-destructive/10"
                @click="handleCancelSchedule">
                <XCircle class="h-4 w-4 mr-2" />
                Cancel Schedule
              </Button>
            </div>
            <p class="text-sm text-muted-foreground">
              {{ scheduleStatus?.description }}
            </p>
            <p v-if="scheduleStatus?.frequency" class="text-xs text-muted-foreground">
              {{ scheduleStatus.frequency }}
            </p>
          </div>
        </div>

        <!-- Warning for Scheduled Campaigns -->
        <div class="flex items-start gap-4 rounded-lg border p-4 text-sm">
          <AlertCircle class="h-4 w-4 text-muted-foreground mt-0.5" />
          <div class="grid gap-1">
            <p class="font-medium">Campaign is scheduled</p>
            <p class="text-muted-foreground">
              The campaign content and recipients are locked while scheduled.
              Cancel the schedule to make changes.
            </p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
