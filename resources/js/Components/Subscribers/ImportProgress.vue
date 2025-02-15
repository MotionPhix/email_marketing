<script setup lang="ts">
import { ref, watch } from 'vue'
import { useToast } from '@/hooks/useToast'

const { toast } = useToast()

const props = defineProps<{
  show: boolean
  total: number
}>()

const emit = defineEmits(['update:show', 'complete'])

const progress = ref(0)
const processed = ref(0)
const status = ref<'processing' | 'completed' | 'error'>('processing')

// Simulated progress updates - in real implementation, this would come from backend
const startProgress = () => {
  const interval = setInterval(() => {
    if (progress.value < 100) {
      progress.value += 5
      processed.value = Math.floor((progress.value / 100) * props.total)
    } else {
      clearInterval(interval)
      status.value = 'completed'
      emit('complete')

      toast({
        title: "Import Complete",
        description: `Successfully imported ${props.total} subscribers`
      })
    }
  }, 500)
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    progress.value = 0
    processed.value = 0
    status.value = 'processing'
    startProgress()
  }
})
</script>

<template>
  <Dialog :open="show" @update:open="$emit('update:show', false)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Importing Subscribers</DialogTitle>
        <DialogDescription>
          {{ status === 'processing' ? 'Please wait while we import your subscribers...' : 'Import completed!' }}
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span>Progress</span>
            <span>{{ processed }} / {{ total }}</span>
          </div>
          <Progress :value="progress" />
        </div>

        <Alert v-if="status === 'completed'" variant="success">
          <CheckCircleIcon class="h-4 w-4" />
          <AlertTitle>Success</AlertTitle>
          <AlertDescription>
            All subscribers have been imported successfully.
          </AlertDescription>
        </Alert>
      </div>

      <DialogFooter>
        <Button
          v-if="status === 'completed'"
          @click="$emit('update:show', false)"
        >
          Close
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
