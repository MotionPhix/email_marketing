<script setup>
import { ref } from 'vue'
import { Button } from '@/Components/ui/button'
import EmailEditor from '@/Components/Campaign/EmailEditor.vue'

const props = defineProps({
  initialContent: {
    type: [String, null],
    default: null
  },
  processing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['back', 'save'])

const editor = ref(null)

const handleSave = async (isDraft = true) => {
  const design = await editor.value?.saveDesign()
  emit('save', { design, isDraft })
}
</script>

<template>
  <div class="fixed inset-0 z-50 bg-background flex flex-col">
    <!-- Header -->
    <div class="border-b p-4 flex items-center justify-between">
      <Button
        variant="outline"
        @click="$emit('back')"
      >
        Back to Details
      </Button>

      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          :disabled="processing"
          @click="handleSave(true)"
        >
          Save as Draft
        </Button>

        <Button
          variant="default"
          :disabled="processing"
          @click="handleSave(false)"
        >
          Save & Continue
        </Button>
      </div>
    </div>

    <!-- Editor -->
    <div class="flex-1">
      <EmailEditor
        ref="editor"
        :initial-design="initialContent ? JSON.parse(initialContent) : null"
        class="h-full w-full"
      />
    </div>
  </div>
</template>
