<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import UITransition from '@/Components/Transition.vue'
import UnlayerEmailEditor from '@/Components/Campaign/UnlayerEmailEditor.vue'
import { Button } from '@/Components/ui/button'
import { formatDistanceToNow } from 'date-fns'

interface EditorProps {
  modelValue: string
  initialContent: string | null
  processing: boolean
  isSaving: boolean
  lastSaved: string | null
}

interface EditorEmits {
  (e: 'update:modelValue', value: string): void
  (e: 'back'): void
  (e: 'save', payload: { design: any; isDraft: boolean }): void
  (e: 'content:change'): void
}

const props = withDefaults(defineProps<EditorProps>(), {
  initialContent: null,
  processing: false,
  isSaving: false,
  lastSaved: null
})

const emit = defineEmits<EditorEmits>()

const editor = ref<InstanceType<typeof UnlayerEmailEditor> | null>(null)
const previewMode = ref(false)
const previewDevice = ref<'desktop' | 'mobile' | 'tablet'>('desktop')
const previewHtml = ref('')
const isExporting = ref(false)

// Handle saving
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
    throw error
  }
}

const handleContentChange = () => {
  emit('content:change')
}

// Handle preview updates
watch(
  [
    () => previewMode.value,
    () => previewDevice.value
  ],
  async ([newPreviewMode]) => {
    if (!newPreviewMode || !editor.value || isExporting.value) return

    try {
      isExporting.value = true
      const result = await editor.value.exportHtml()
      if (result?.html) {
        previewHtml.value = result.html
      }
    } catch (error) {
      console.error('Error exporting HTML:', error)
      previewHtml.value = '<div class="p-4 text-red-500">Error loading preview</div>'
    } finally {
      isExporting.value = false
    }
  }
)

// Computed properties
const lastSavedText = computed(() => {
  if (!props.lastSaved) return ''
  try {
    const date = new Date(props.lastSaved)
    return `Last saved ${formatDistanceToNow(date)} ago`
  } catch (error) {
    return 'Last saved recently'
  }
})

const shouldShowMobileView = computed(() =>
  previewMode.value && previewDevice.value === 'mobile'
)

// Expose methods to parent
defineExpose({
  saveDesign: handleSave,
  exportHtml: () => editor.value?.exportHtml()
})
</script>

<template>
  <div class="fixed inset-0 z-50 bg-background flex flex-col">
    <!-- Header -->
    <header class="border-b p-4 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          @click="emit('back')"
        >
          Back to Details
        </Button>

        <span
          v-if="lastSaved"
          class="text-sm text-muted-foreground"
          :title="lastSaved"
        >
          {{ lastSavedText }}
        </span>

        <span
          v-if="isSaving"
          class="text-sm text-muted-foreground animate-pulse"
        >
          Saving...
        </span>
      </div>

      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          :disabled="processing || isSaving"
          @click="handleSave(true)"
        >
          Save Draft
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
          :class="{ 'max-w-md mx-auto': shouldShowMobileView }"
        >
          <div
            class="h-full w-full overflow-auto"
            v-html="previewHtml"
          />
        </div>

        <div v-else class="h-full">
          <UnlayerEmailEditor
            ref="editor"
            :model-value="modelValue"
            :initial-design="initialContent ? JSON.parse(initialContent) : null"
            class="h-full w-full"
            @update:model-value="$emit('update:modelValue', $event)"
            @change="handleContentChange"
          />
        </div>
      </UITransition>
    </main>
  </div>
</template>
