<script setup lang="ts">
import { ref, watch } from 'vue'
import {EmailEditor} from 'vue-email-editor'
import {useDark} from "@vueuse/core";

interface Props {
  modelValue: string
  mergeTags?: Record<string, any>
  customCSS?: string[]
  tools?: Record<string, any>
  appearance?: Record<string, any>
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'ready'): void
  (e: 'design:updated', design: any): void
  (e: 'error', error: Error): void
}>()

const editor = ref<any>(null)
const isDark = useDark()

// Default merge tags for email personalization
const defaultMergeTags = {
  subscriber: {
    name: 'Subscriber',
    mergeTags: {
      first_name: {
        name: 'First Name',
        value: '{{subscriber.first_name}}',
      },
      last_name: {
        name: 'Last Name',
        value: '{{subscriber.last_name}}',
      },
      email: {
        name: 'Email',
        value: '{{subscriber.email}}',
      },
      company: {
        name: 'Company',
        value: '{{subscriber.company}}',
      },
    },
  },
  campaign: {
    name: 'Campaign',
    mergeTags: {
      name: {
        name: 'Campaign Name',
        value: '{{campaign.name}}',
      },
      subject: {
        name: 'Subject',
        value: '{{campaign.subject}}',
      },
      unsubscribe_url: {
        name: 'Unsubscribe URL',
        value: '{{campaign.unsubscribe_url}}',
      },
      web_view_url: {
        name: 'Web View URL',
        value: '{{campaign.web_view_url}}',
      },
    },
  },
}

// SendGrid specific tools and configurations
const sendGridConfig = {
  fonts: {
    showDefaultFonts: true,
    customFonts: [
      {
        label: 'Inter',
        value: "'Inter', sans-serif",
        url: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
      },
    ],
  },
  blocks: [
    // SendGrid specific blocks
    {
      name: 'sendgrid/dynamic',
      label: 'Dynamic Content',
      category: 'SendGrid',
      icon: '<svg>...</svg>', // Add your icon SVG
      render: ({ values }) => {
        return `
          {{#each ${values.variable}}}
            ${values.content}
          {{/each}}
        `
      },
      values: {
        variable: 'items',
        content: '<p>Dynamic content here</p>',
      },
      options: {
        variable: {
          title: 'Variable Name',
          type: 'text',
        },
        content: {
          title: 'Content Template',
          type: 'rich-text',
        },
      },
    },
  ],
}

// Configure the editor options
const editorOptions = {
  appearance: {
    theme: isDark ? 'dark' : 'white',
    panels: {
      tools: {
        dock: 'right',
      },
    },
    colors: {
      primary: 'rgb(var(--primary))',
      secondary: 'rgb(var(--secondary))',
    },
  },
  tools: {
    ...sendGridConfig.blocks,
    ...props.tools,
  },
  mergeTags: {
    ...defaultMergeTags,
    ...props.mergeTags,
  },
  fonts: sendGridConfig.fonts,
  features: {
    colorPicker: {
      presets: [
        '#000000',
        '#FFFFFF',
        'rgb(var(--primary))',
        'rgb(var(--secondary))',
        'rgb(var(--accent))',
      ],
    },
  },
  customCSS: [
    ...(props.customCSS || []),
    // Dark mode support
    `
    [data-theme='dark'] {
      --unlayer-bg-color: rgb(var(--background));
      --unlayer-text-color: rgb(var(--foreground));
      --unlayer-border-color: rgb(var(--border));
    }
    `,
    // SendGrid specific styles
    `
    .blockbuilder-layer {
      font-family: Inter, system-ui, sans-serif;
    }
    `,
  ],
}

// Editor methods
const loadDesign = async (design: string) => {
  try {
    if (!editor.value) return
    const parsedDesign = JSON.parse(design)
    await editor.value.loadDesign(parsedDesign)
    emit('design:updated', parsedDesign)
  } catch (error) {
    emit('error', error as Error)
  }
}

const saveDesign = async () => {
  try {
    if (!editor.value) return
    const design = await editor.value.saveDesign()
    emit('update:modelValue', JSON.stringify(design))
    emit('design:updated', design)
    return design
  } catch (error) {
    emit('error', error as Error)
    return null
  }
}

const exportHtml = async () => {
  try {
    if (!editor.value) return
    const { html, design } = await editor.value.exportHtml()
    return {
      html,
      design: JSON.stringify(design),
    }
  } catch (error) {
    emit('error', error as Error)
    return null
  }
}

// Watch for theme changes
watch(() => isDark, (newTheme) => {
  if (!editor.value) return
  editor.value.updateAppearance({
    theme: newTheme === 'dark' ? 'dark' : 'white',
  })
})

// Watch for design changes
watch(() => props.modelValue, (newValue) => {
  if (!newValue) return
  loadDesign(newValue)
})

// Expose methods to parent
defineExpose({
  loadDesign,
  saveDesign,
  exportHtml,
})

// Handle editor ready
const onEditorReady = () => {
  if (props.modelValue) {
    loadDesign(props.modelValue)
  }
  emit('ready')
}
</script>

<template>
  <div class="min-h-screen relative">
    <EmailEditor
      ref="editor"
      :min-height="'100vh'"
      :options="editorOptions"
      @ready="onEditorReady"
    />
  </div>
</template>

<style>
iframe {
  @apply !min-h-screen;
}
</style>
