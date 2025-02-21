<script setup lang="ts">
import { ref } from 'vue'
import { EmailEditor } from 'vue-email-editor'
import {useDark} from "@vueuse/core";

interface EmailEditorRef {
  editor: {
    saveDesign: (callback: (design: any) => void) => void;
    exportHtml: (callback: (data: { design: any; html: string }) => void) => void;
    loadDesign: (design: any) => void;
  }
}

// Editor reference
const emailEditor = ref<EmailEditorRef | null>(null)

// Editor state
const errorMessage = ref('')
const isDark = useDark()

// Editor configuration
const minHeight = '100vh'
const locale = 'en'
const projectId = import.meta.env.VITE_UNLAYER_PROJECT_ID

// Custom merge tags configuration
const mergeTags = {
  subscriber: {
    name: 'Subscriber',
    mergeTags: {
      email: {
        name: 'Email',
        value: '{{subscriber.email}}',
      },
      first_name: {
        name: 'First Name',
        value: '{{subscriber.first_name}}',
      },
      last_name: {
        name: 'Last Name',
        value: '{{subscriber.last_name}}',
      },
      full_name: {
        name: 'Full Name',
        value: '{{subscriber.full_name}}',
      },
    },
  },
  campaign: {
    name: 'Campaign',
    mergeTags: {
      subject: {
        name: 'Subject',
        value: '{{campaign.subject}}',
      },
      sender_name: {
        name: 'Sender Name',
        value: '{{campaign.sender_name}}',
      },
      sender_email: {
        name: 'Sender Email',
        value: '{{campaign.sender_email}}',
      },
      preview_text: {
        name: 'Preview Text',
        value: '{{campaign.preview_text}}',
      },
    },
  },
  urls: {
    name: 'URLs',
    mergeTags: {
      unsubscribe: {
        name: 'Unsubscribe URL',
        value: '{{urls.unsubscribe}}',
      },
      webview: {
        name: 'Web View URL',
        value: '{{urls.webview}}',
      },
    },
  },
}

// Custom blocks configuration
const customBlocks = [
  {
    name: 'custom/countdown',
    label: 'Countdown Timer',
    category: 'Custom',
    icon: '<svg viewBox="0 0 24 24" width="24" height="24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm1-8h4v2h-6V7h2v5z"/></svg>',
    content: `
      <div style="text-align: center; padding: 20px;">
        <div class="countdown" data-end-date="{endDate}">
          Coming Soon!
        </div>
      </div>
    `,
    values: {
      endDate: new Date().toISOString(),
    },
    options: {
      endDate: {
        label: 'End Date',
        type: 'date',
        defaultValue: new Date().toISOString(),
      },
    },
  },
  {
    name: 'custom/social',
    label: 'Social Links',
    category: 'Custom',
    icon: '<svg viewBox="0 0 24 24" width="24" height="24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z"/></svg>',
    content: `
      <div style="text-align: center; padding: 20px;">
        <div class="social-links">
          {{#each social}}
          <a href="{{url}}" style="margin: 0 10px;">
            <img src="{{icon}}" alt="{{platform}}" style="width: 32px; height: 32px;">
          </a>
          {{/each}}
        </div>
      </div>
    `,
    values: {
      social: [
        { platform: 'Facebook', url: '', icon: '/icons/facebook.png' },
        { platform: 'Twitter', url: '', icon: '/icons/twitter.png' },
        { platform: 'Instagram', url: '', icon: '/icons/instagram.png' },
      ],
    },
    options: {
      social: {
        label: 'Social Links',
        type: 'repeater',
        properties: {
          platform: {
            label: 'Platform',
            type: 'text',
          },
          url: {
            label: 'URL',
            type: 'text',
          },
          icon: {
            label: 'Icon URL',
            type: 'text',
          },
        },
      },
    },
  },
]

// Tools configuration
const tools = {
  image: {
    enabled: true,
    properties: {
      // Add custom image properties
      imageOptimization: true,
      cropAspectRatio: '16:9',
    },
  },
  text: {
    enabled: true,
    properties: {
      // Add custom text properties
      textStyles: true,
      lineHeight: true,
      letterSpacing: true,
    },
  },
  button: {
    enabled: true,
    properties: {
      // Add custom button properties
      borderRadius: true,
      padding: true,
      hoverColor: true,
    },
  },
  // Add custom blocks
  custom: {
    enabled: true,
    items: customBlocks,
  },
}

// Appearance configuration
const appearance = {
  theme: isDark ? 'dark' : 'light',
  panels: {
    tools: {
      dock: 'right',
    },
  },
  colors: {
    primary: 'rgb(var(--primary))',
    secondary: 'rgb(var(--secondary))',
    tertiary: 'rgb(var(--accent))',
    background: 'rgb(var(--background))',
  },
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
}

// Editor options
const options = {
  features: {
    preview: true,
    colorPicker: {
      presets: [
        '#000000',
        '#FFFFFF',
        'rgb(var(--primary))',
        'rgb(var(--secondary))',
        'rgb(var(--accent))',
      ],
    },
    textEditor: {
      spellChecker: true,
      tables: true,
      cleanPaste: true,
    },
  },
  mergeTags,
  displayMode: 'email',
  customCSS: [
    `
    .unlayer-editor {
      font-family: Inter, system-ui, -apple-system, sans-serif;
    }
    [data-theme='dark'] {
      --unlayer-color-bg: rgb(var(--background));
      --unlayer-color-text: rgb(var(--foreground));
    }
    `,
  ],
}

// Editor event handlers
const editorLoaded = () => {
  console.log('Editor loaded')
}

const editorReady = () => {
  console.log('Editor ready')
}

// Editor actions
const saveDesign = () => {
  if (!emailEditor.value?.editor) {
    errorMessage.value = 'Editor not initialized'
    console.error('Editor not initialized')
    return
  }

  emailEditor.value.editor.saveDesign((design) => {
    console.log('Design saved:', design)
  })
}

const exportHtml = () => {
  if (!emailEditor.value?.editor) {
    errorMessage.value = 'Editor not initialized'
    console.error('Editor not initialized')
    return
  }

  emailEditor.value.editor.exportHtml((data) => {
    console.log('HTML exported:', data)
  })
}

// Expose methods to parent components
defineExpose({
  saveDesign,
  exportHtml
})
</script>

<template>
<!--  <div class="h-full">-->
<!--    <EmailEditor-->
<!--      ref="emailEditor"-->
<!--      :appearance="appearance"-->
<!--      :min-height="minHeight"-->
<!--      :project-id="Number(projectId)"-->
<!--      :locale="locale"-->
<!--      :tools="tools"-->
<!--      :options="options"-->
<!--      @load="editorLoaded"-->
<!--      @ready="editorReady"-->
<!--      class="h-full"-->
<!--    />-->
<!--  </div>-->

  <div class="h-full relative">
    <div
      class="email-editor-container">
      <EmailEditor
        ref="emailEditor"
        :appearance="appearance"
        :min-height="minHeight"
        :project-id="Number(projectId)"
        :locale="locale"
        :tools="tools"
        :options="options"
        @load="editorLoaded"
        @ready="editorReady"
        class="h-full"
      />
    </div>
  </div>
</template>

<style>
iframe {
  @apply !min-h-screen;
}

/* Custom block styles */
.countdown {
  font-size: 24px;
  font-weight: bold;
  color: rgb(var(--primary));
}

.social-links {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.social-links img {
  transition: transform 0.2s;
}

.social-links img:hover {
  transform: scale(1.1);
}

/* Custom block styles */
.countdown {
  font-size: 24px;
  font-weight: bold;
  color: rgb(var(--primary));
}

.social-links {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.social-links img {
  transition: transform 0.2s;
}

.social-links img:hover {
  transform: scale(1.1);
}

/* Loading and error styles */
.loader {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  font-size: 20px;
  color: rgb(var(--primary));
}

.error-message {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  font-size: 18px;
  color: red;
}

/* Merge tag dropdown styles */
.merge-tag-dropdown {
  margin-bottom: 10px;
}
</style>
