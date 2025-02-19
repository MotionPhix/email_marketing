<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {EmailEditor} from 'vue-email-editor'
import { toast } from 'vue-sonner'
import { formatDistanceToNow } from 'date-fns'
import {TransitionChild, TransitionRoot} from "@headlessui/vue";

interface Props {
  modelValue: string
  initialContent: string | null
  previewMode: boolean
  previewDevice: 'desktop' | 'mobile' | 'tablet'
  isSaving: boolean
  lastSaved: string | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'back'): void
  (e: 'save', payload: { design: string; isDraft: boolean }): void
  (e: 'content-change'): void
}>()

const editor = ref<InstanceType<typeof EmailEditor> | null>(null)
const previewHtml = ref('')
const isExporting = ref(false)

const lastSavedText = computed(() => {
  if (!props.lastSaved) return ''
  return `Last saved ${formatDistanceToNow(new Date(props.lastSaved))} ago`
})

// Editor configuration
const editorConfig = {
  tools: {
    // Configure available design tools
    heading: {
      properties: {
        text: {
          value: 'Hello World'
        }
      }
    }
  },
  appearance: {
    theme: 'white',
    panels: {
      tools: {
        dock: 'left'
      }
    }
  },
  customCSS: [
    // Add any custom CSS for the editor
    `.blockbuilder-layer { font-family: Inter, sans-serif; }`,
  ]
}

// Methods
const handleSave = async (isDraft = true) => {
  try {
    if (!editor.value) {
      throw new Error('Editor not initialized')
    }

    const design = await editor.value.saveDesign()
    emit('save', { design: JSON.stringify(design), isDraft })
  } catch (error) {
    console.error('Error saving design:', error)
    toast.error('Failed to save email design')
  }
}

const handleContentChange = () => {
  emit('content-change')
}

const loadDesign = async () => {
  if (!editor.value || !props.initialContent) return

  try {
    const design = JSON.parse(props.initialContent)
    await editor.value.loadDesign(design)
  } catch (error) {
    console.error('Error loading design:', error)
    toast.error('Failed to load email design')
  }
}

const exportHtml = async () => {
  if (!editor.value) return

  try {
    isExporting.value = true
    const result = await editor.value.exportHtml()
    previewHtml.value = result.html || ''
  } catch (error) {
    console.error('Error exporting HTML:', error)
    toast.error('Failed to generate preview')
  } finally {
    isExporting.value = false
  }
}

// Watch for preview mode changes
watch(
  () => props.previewMode,
  async (newValue) => {
    if (newValue) {
      await exportHtml()
    }
  }
)

// Handle editor ready event
const onEditorReady = async () => {
  await loadDesign()
}
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
        <!-- Preview Device Selector -->
        <Select
          :model-value="previewDevice"
          :disabled="!previewMode">
          <SelectTrigger class="w-32">
            <SelectValue placeholder="Preview Device" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="desktop">Desktop</SelectItem>
            <SelectItem value="tablet">Tablet</SelectItem>
            <SelectItem value="mobile">Mobile</SelectItem>
          </SelectContent>
        </Select>

        <!-- Preview Toggle -->
        <Button
          variant="outline"
          :disabled="isSaving"
          @click="previewMode = !previewMode">
          {{ previewMode ? 'Edit' : 'Preview' }}
        </Button>

        <Button
          variant="outline"
          :disabled="isSaving"
          @click="handleSave(true)">
          Save as Draft
        </Button>

        <Button
          variant="default"
          :disabled="isSaving"
          @click="handleSave(false)">
          Save & Continue
        </Button>
      </div>
    </header>

    <!-- Editor/Preview Area -->
    <main class="flex-1 relative">
      <TransitionRoot
        appear
        show
        as="template">
        <div class="h-full">
          <!-- Preview Mode -->
          <TransitionChild
            v-if="previewMode"
            enter="transition ease-out duration-200"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="transition ease-in duration-150"
            leave-from="opacity-100"
            leave-to="opacity-0"
            class="absolute inset-0 bg-background"
            :class="{
              'max-w-md mx-auto': previewDevice === 'mobile',
              'max-w-2xl mx-auto': previewDevice === 'tablet'
            }">
            <div
              class="h-full w-full overflow-auto"
              v-html="previewHtml"
            />
          </TransitionChild>

          <!-- Editor Mode -->
          <TransitionChild
            v-else
            enter="transition ease-out duration-200"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="transition ease-in duration-150"
            leave-from="opacity-100"
            leave-to="opacity-0"
            class="h-full">
            <EmailEditor
              ref="editor"
              :config="editorConfig"
              @ready="onEditorReady"
              @change="handleContentChange"
            />
          </TransitionChild>
        </div>
      </TransitionRoot>
    </main>
  </div>
</template>
