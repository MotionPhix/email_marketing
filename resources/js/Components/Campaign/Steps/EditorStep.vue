<script setup lang="ts">
import { ref, watch, onBeforeUnmount, computed } from 'vue'
import UITransition from '@/Components/Transition.vue'
import UnlayerEmailEditor from '@/Components/Campaign/UnlayerEmailEditor.vue'
import { formatDistanceToNow } from 'date-fns'
import { toast } from 'vue-sonner'

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
const isExporting = ref(false)
const previewHtml = ref('')
const isEditorReady = ref(false)
const isLoading = ref(false)

const onEditorReady = () => {
  isEditorReady.value = true
  console.log('Editor is ready')
}

const handleSave = (isDraft = true) => {
  if (!editor.value) {
    console.error('Editor reference is null')
    toast.error('Editor is not ready. Please try again.')
    return
  }

  isLoading.value = true
  console.log('Attempting to save design...')

  editor.value.saveDesign((design) => {
    if (!design) {
      console.error('No design data returned')
      toast.error('Failed to save design. Please try again.')
      isLoading.value = false
      return
    }

    emit('save', { design, isDraft })
    toast.success('Design saved successfully')
    isLoading.value = false
  })
}

const handleContentChange = () => {
  emit('content:change')
}

const togglePreviewMode = () => {
  previewMode.value = !previewMode.value
  console.log(`Preview mode: ${previewMode.value}`)
}

const exportHtml = () => {
  if (!editor.value) {
    console.error('Editor reference is null')
    toast.error('Editor is not ready. Please try again.')
    return
  }

  isExporting.value = true
  console.log('Attempting to export design...')

  editor.value.exportHtml((data) => {
    if (!data || !data.html) {
      console.error('No HTML data returned')
      toast.error('Failed to export HTML. Please try again.')
      isExporting.value = false
      return
    }

    previewHtml.value = data.html
    console.log('Design exported successfully')
    toast.success('Design exported successfully')
    isExporting.value = false
  })
}

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue && editor.value) {
      editor.value.loadDesign(JSON.parse(newValue))
    }
  }
)

watch(
  [() => previewMode.value, () => previewDevice.value],
  ([newPreviewMode]) => {
    if (!newPreviewMode || !editor.value || isExporting.value) return

    isExporting.value = true

    try {
      editor.value.exportHtml((data) => {
        if (data?.html) {
          previewHtml.value = data.html
        }
        isExporting.value = false
      })
    } catch (error) {
      console.error('Error exporting HTML:', error)
      previewHtml.value = '<div class="p-4 text-red-500">Error loading preview</div>'
      isExporting.value = false
    }
  }
)

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

onBeforeUnmount(() => {
  isExporting.value = false
  previewMode.value = false
  previewHtml.value = ''
})

defineExpose({
  saveDesign: handleSave,
  exportHtml: () => {
    if (!editor.value) return
    return new Promise((resolve) => {
      editor.value.exportHtml((data) => resolve(data))
    })
  },
  isReady: isEditorReady
})
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

        <!-- Preview controls -->
        <div class="flex items-center space-x-2">
          <Button
            size="sm"
            variant="outline"
            :class="{ 'bg-primary text-primary-foreground': previewMode }"
            @click="togglePreviewMode">
            {{ previewMode ? 'Edit' : 'Preview' }}
          </Button>

          <div v-if="previewMode" class="flex items-center space-x-1">
            <Button
              v-for="device in ['desktop', 'tablet', 'mobile']"
              :key="device"
              size="sm"
              variant="outline"
              :class="{ 'bg-primary text-primary-foreground': previewDevice === device }"
              @click="previewDevice = device">
              {{ device }}
            </Button>
          </div>
        </div>

        <span
          v-if="props.lastSaved"
          class="text-sm text-muted-foreground"
          :title="props.lastSaved">
          {{ lastSavedText }}
        </span>

        <span
          v-if="props.isSaving"
          class="text-sm text-muted-foreground animate-pulse">
          Saving...
        </span>
      </div>

      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          @click="handleSave(true)">
          <template v-if="isLoading">
            <span class="animate-spin mr-2">⌛</span>
          </template>
          Save Draft
        </Button>

        <Button
          variant="default"
          :disabled="props.processing || props.isSaving || !isEditorReady || isLoading"
          @click="handleSave(false)">
          <template v-if="isLoading">
            <span class="animate-spin mr-2">⌛</span>
          </template>
          Save & Continue
        </Button>
      </div>
    </header>

    <!-- Editor/Preview -->
    <main class="flex-1 relative overflow-hidden">
      <UITransition>
        <div
          v-if="previewMode"
          class="absolute inset-0 bg-background"
          :class="{ 'max-w-md mx-auto': shouldShowMobileView }">
          <div class="h-full w-full overflow-auto">
            <div v-if="previewHtml" v-html="previewHtml" />
            <div v-else class="p-4 text-muted-foreground">
              Loading preview...
            </div>
          </div>
        </div>

        <div v-else class="h-full">
          <UnlayerEmailEditor
            ref="editor"
            :model-value="props.modelValue"
            :initial-design="props.initialContent ? JSON.parse(props.initialContent) : null"
            class="h-full w-full"
            @update:model-value="$emit('update:modelValue', $event)"
            @change="handleContentChange"
            @ready="onEditorReady"
          />
        </div>
      </UITransition>
    </main>
  </div>
</template>

<style scoped>
.email-editor-container {
  height: 80%;
}

.preview-mode-container {
  height: 80%;
  overflow: auto;
}

.actions {
  display: flex;
  justify-content: space-around;
  margin-top: 10px;
}
</style>
