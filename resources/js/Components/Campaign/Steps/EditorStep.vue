<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import UITransition from '@/Components/Transition.vue'
import EmailEditor from '@/Components/Campaign/EmailEditor.vue'
import { formatDistanceToNow } from 'date-fns'
import type { EditorProps, EditorEmits, EmailDesign } from '@/types'

const props = withDefaults(defineProps<EditorProps>(), {
  initialContent: null,
  processing: false,
  previewMode: false,
  previewDevice: 'desktop',
  isSaving: false,
  lastSaved: null
})

const emit = defineEmits<EditorEmits>()

const editor = ref<InstanceType<typeof EmailEditor> | null>(null)
const previewHtml = ref('')
const isExporting = ref(false)

const handleSave = async (isDraft = true) => {
  try {
    if (!editor.value) {
      throw new Error('Editor not initialized')
    }

    const design = await editor.value.saveDesign()
    if (!design) {
      throw new Error('Failed to save design')
    }

    emit('save', { design, isDraft })
  } catch (error) {
    console.error('Error saving design:', error)
    // Here you might want to show a toast notification or handle the error in some way
  }
}

const handleContentChange = () => {
  emit('content-change')
}

// Update preview when content changes or preview mode is toggled
watch(
  [
    () => props.previewMode,
    () => props.previewDevice
  ],
  async ([newPreviewMode]) => {
    if (!newPreviewMode || !editor.value || isExporting.value) return

    try {
      isExporting.value = true
      const result = await editor.value.exportHtml()
      const html = result?.html

      if (typeof html === 'string') {
        previewHtml.value = html
      } else {
        throw new Error('Invalid HTML export result')
      }
    } catch (error) {
      console.error('Error exporting HTML:', error)
      previewHtml.value = '<div class="p-4 text-red-500">Error loading preview</div>'
    } finally {
      isExporting.value = false
    }
  },
  {
    immediate: true
  }
)

const lastSavedText = computed(() => {
  if (!props.lastSaved) return ''

  try {
    const date = new Date(props.lastSaved)
    return `Last saved ${formatDistanceToNow(date)} ago`
  } catch (error) {
    console.error('Error formatting date:', error)
    return 'Last saved recently'
  }
})

const shouldShowMobileView = computed(() =>
  props.previewMode && props.previewDevice === 'mobile'
)
</script>

<template>
  <div class="fixed inset-0 z-50 bg-background flex flex-col">
    <!-- Header -->
    <header class="border-b p-4 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          @click="emit('back')">
          Back to Details
        </Button>

        <span
          v-if="lastSaved"
          class="text-sm text-muted-foreground"
          :title="props.lastSaved || ''">
          {{ lastSavedText }}
        </span>

        <span
          v-if="isSaving"
          class="text-sm text-muted-foreground animate-pulse">
          Saving...
        </span>
      </div>

      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          :disabled="processing || isSaving"
          @click="handleSave(true)"
        >
          Save as Draft
        </Button>

        <Button
          variant="default"
          :disabled="processing || isSaving"
          @click="handleSave(false)"
        >
          Save & Continue
        </Button>
      </div>
    </header>

    <!-- Editor/Preview -->
    <main class="flex-1 relative">
      <UITransition>
        <div
          v-if="previewMode"
          class="absolute inset-0 bg-background"
          :class="{ 'max-w-md mx-auto': shouldShowMobileView }">
          <div
            class="h-full w-full overflow-auto"
            v-html="previewHtml"
          />
        </div>

        <div v-else class="h-full">
          <EmailEditor
            ref="editor"
            :model-value="modelValue"
            :initial-design="initialContent ? JSON.parse(initialContent) : null"
            class="h-full w-full"
            @update:model-value="emit('update:modelValue', $event)"
            @change="handleContentChange"
          />
        </div>
      </UITransition>
    </main>
  </div>
</template>
