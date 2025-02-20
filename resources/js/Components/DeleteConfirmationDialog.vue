<script setup lang="ts">
defineProps<{
  show: boolean
  title: string
  description: string
  processing?: boolean
}>()

const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <Dialog :open="show" @close="$emit('close')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>
          {{ description }}
        </DialogDescription>
      </DialogHeader>

      <div class="mt-6 flex items-center justify-end space-x-4">
        <Button
          type="button"
          variant="ghost"
          @click="$emit('close')"
          :disabled="processing">
          Cancel
        </Button>

        <Button
          type="button"
          variant="destructive"
          @click="$emit('confirm')"
          :disabled="processing"
          :class="{ 'opacity-75': processing }">
          <span v-if="processing">Deleting...</span>
          <span v-else>Delete</span>
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
