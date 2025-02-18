<script setup lang="ts">
import {ref, onMounted, watch, onBeforeUnmount} from 'vue'
import EmailEditor from 'vue-email-editor'
import type { Variable } from '@/config/variables'
import { availableVariables } from '@/config/variables'

const props = defineProps<{
  modelValue: string
  templateId?: string | null
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const emailEditorRef = ref()
const isLoading = ref(true)

// Convert our variables to Unlayer merge tags format
const mergeTags = {
  subscriber: {
    name: 'Subscriber',
    items: availableVariables
      .filter(v => v.category === 'subscriber')
      .map(v => ({
        name: v.name,
        value: v.key,
        sample: v.key.includes('first_name') ? 'John' :
          v.key.includes('last_name') ? 'Doe' :
            v.key.includes('email') ? 'john.doe@example.com' : ''
      }))
  },
  campaign: {
    name: 'Campaign',
    items: availableVariables
      .filter(v => v.category === 'campaign')
      .map(v => ({
        name: v.name,
        value: v.key,
        sample: v.key.includes('name') ? 'February Newsletter' : ''
      }))
  },
  system: {
    name: 'System',
    items: availableVariables
      .filter(v => v.category === 'system')
      .map(v => ({
        name: v.name,
        value: v.key,
        sample: v.key.includes('date') ? new Date().toLocaleDateString() :
          v.key.includes('unsubscribe') ? 'http://example.com/unsubscribe' :
            v.key.includes('web_version') ? 'http://example.com/web' : ''
      }))
  }
}

const editorConfig = {
  tools: {
    heading: {
      properties: {
        text: {
          value: 'Hello {{subscriber.first_name}}!'
        }
      }
    }
  },
  mergeTags,
  appearance: {
    theme: 'white',
    panels: {
      tools: {
        dock: 'left'
      }
    }
  },
  features: {
    stockImages: true,
    undoRedo: true,
    textEditor: {
      spellChecker: true,
      tables: true,
      cleanPaste: true,
    }
  }
}

const onReady = () => {
  isLoading.value = false

  // If we have a template ID, load it
  if (props.templateId) {
    loadTemplate()
  }
  // If we have existing content, load it
  else if (props.modelValue) {
    emailEditorRef.value.editor.loadDesign(JSON.parse(props.modelValue))
  }
}

const loadTemplate = async () => {
  if (!props.templateId) return

  try {
    const response = await fetch(`/api/templates/${props.templateId}`)
    const template = await response.json()

    if (template.design) {
      emailEditorRef.value.editor.loadDesign(JSON.parse(template.design))
    }
  } catch (error) {
    console.error('Failed to load template:', error)
  }
}

const saveDesign = () => {
  emailEditorRef.value.editor.saveDesign(design => {
    emit('update:modelValue', JSON.stringify(design))
  })
}

const exportHtml = () => {
  emailEditorRef.value.editor.exportHtml(data => {
    const { html, design } = data
    // Store both the HTML and the design
    emit('update:modelValue', JSON.stringify(design))
  })
}

// Watch for template changes
watch(() => props.templateId, (newId) => {
  if (newId && !isLoading.value) {
    loadTemplate()
  }
})

// Auto-save every 30 seconds
let autoSaveInterval: number

onMounted(() => {
  autoSaveInterval = window.setInterval(saveDesign, 30000)
})

onBeforeUnmount(() => {
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval)
  }
})
</script>

<template>
  <div class="relative min-h-[700px] w-full">
    <!-- Loading overlay -->
    <div
      v-if="isLoading"
      class="absolute inset-0 z-10 flex items-center justify-center bg-white/80"
    >
      <div class="text-center">
        <Spinner class="mx-auto h-8 w-8" />
        <p class="mt-2 text-sm text-gray-600">Loading editor...</p>
      </div>
    </div>

    <!-- Unlayer Email Editor -->
    <EmailEditor
      ref="emailEditorRef"
      :options="editorConfig"
      @ready="onReady"
      class="h-[700px] w-full"
    />
  </div>
</template>

<style scoped>
/* Fix for Unlayer editor in dark mode */
:deep(.unlayer-editor) {
  @apply bg-white;
}

/* Ensure the editor takes full height */
:deep(.unlayer-editor-frame) {
  min-height: 700px !important;
}
</style>
