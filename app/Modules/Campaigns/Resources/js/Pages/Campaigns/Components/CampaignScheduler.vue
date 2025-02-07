<script setup lang="ts">
import { ref, computed } from 'vue'
import { formatISO } from 'date-fns'
import {
  IconClock,
  IconCalendar,
  IconSend,
  IconCalendarTime
} from '@tabler/icons-vue'

interface Props {
  campaignId: string
  currentStatus: string
}

const props = defineProps<Props>()
const emit = defineEmits(['scheduled', 'sendNow'])

const showScheduler = ref(false)
const scheduledDate = ref('')
const scheduledTime = ref('')

const minDateTime = computed(() => {
  const now = new Date()
  return formatISO(now, { representation: 'date' })
})

const scheduleDateTime = () => {
  const dateTime = new Date(`${scheduledDate.value}T${scheduledTime.value}`)

  emit('scheduled', {
    campaignId: props.campaignId,
    scheduledAt: dateTime.toISOString()
  })

  showScheduler.value = false
}

const sendNow = () => {
  emit('sendNow', {
    campaignId: props.campaignId
  })
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-4">
      <Button
        v-if="currentStatus === 'draft'"
        variant="default"
        @click="sendNow"
      >
        <TablerSend class="mr-2 h-4 w-4" />
        Send Now
      </Button>

      <Button
        v-if="currentStatus === 'draft'"
        variant="outline"
        @click="showScheduler = true"
      >
        <TablerClock class="mr-2 h-4 w-4" />
        Schedule
      </Button>
    </div>

    <Dialog :open="showScheduler" @update:open="showScheduler = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Schedule Campaign</DialogTitle>
          <DialogDescription>
            Choose when you want to send this campaign
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label>Date</Label>
            <Input
              type="date"
              v-model="scheduledDate"
              :min="minDateTime"
            />
          </div>

          <div class="grid gap-2">
            <Label>Time</Label>
            <Input
              type="time"
              v-model="scheduledTime"
            />
          </div>
        </div>

        <DialogFooter>
          <Button variant="ghost" @click="showScheduler = false">
            Cancel
          </Button>
          <Button
            variant="default"
            :disabled="!scheduledDate || !scheduledTime"
            @click="scheduleDateTime"
          >
            <TablerCalendar class="mr-2 h-4 w-4" />
            Schedule
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
