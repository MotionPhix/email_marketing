<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { EmailEditor } from 'vue-email-editor'
import {
  ArrowLeftIcon,
  ArrowRightIcon,
  PlusIcon,
  SearchIcon,
  CheckIcon
} from 'lucide-vue-next'
import { ScrollArea } from '@/Components/ui/scroll-area'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from '@/Components/ui/sheet'
import {useDark, useStorage} from '@vueuse/core'
import {EmailTemplate} from "@/types/campaign";

const props = defineProps<{
  templates?: EmailTemplate[]
  formData?: {
    template?: EmailTemplate
  }
}>()

// Template types with descriptions
const templateTypes = [
  {
    value: 'drag-drop',
    label: 'Visual Builder',
    description: 'Design your email using our drag-and-drop builder',
    icon: 'MousePointerClick'
  },
  {
    value: 'html',
    label: 'HTML Editor',
    description: 'Write your email using HTML and CSS',
    icon: 'Code2'
  },
  {
    value: 'markdown',
    label: 'Markdown Editor',
    description: 'Write your email using Markdown syntax',
    icon: 'FileText'
  }
] as const

const emit = defineEmits(['back', 'next'])

// Template selection and editor state
const selectedTemplate = ref<EmailTemplate | null>(props.formData?.template || null)
const searchQuery = ref('')
const activeCategory = useStorage('template_category', 'all')
const emailEditorRef = ref()
const isEditorOpen = ref(false)
const editorStep = useStorage('editor_step', 1)
const isLoading = ref(false)
const errors = ref({})
const projectId = import.meta.env.VITE_UNLAYER_PROJECT_ID
const isDark = useDark()

const tempTemplate = ref<Partial<EmailTemplate>>({
  name: '',
  description: '',
  subject: '',
  preview_text: '',
  category: 'newsletter',
  type: 'drag-drop', // Default to drag-drop builder
  is_default: false,
  variables: {},
  design: null,
  tags: []
})

// Show different editors based on template type
const showTemplateEditor = computed(() => {
  return tempTemplate.value.type === 'drag-drop'
})

const showHtmlEditor = computed(() => {
  return tempTemplate.value.type === 'html'
})

const showMarkdownEditor = computed(() => {
  return tempTemplate.value.type === 'markdown'
})

// Handle type change
const handleTypeChange = (type: EmailTemplate['type']) => {
  // Ask for confirmation if changing from drag-drop and design exists
  if (tempTemplate.value.type === 'drag-drop' &&
    tempTemplate.value.design &&
    type !== 'drag-drop') {
    if (!confirm('Changing template type will reset your current design. Continue?')) {
      return
    }
  }

  tempTemplate.value.type = type
  tempTemplate.value.design = null
  tempTemplate.value.content = ''
}

const validateTemplate = (template: Partial<EmailTemplate>) => {
  const errors: Record<string, string> = {}

  if (!template.name) {
    errors['name'] = 'Template name is required'
  } else if (template.name.length > 255) {
    errors['name'] = 'Template name cannot exceed 255 characters'
  }

  if (!template.subject) {
    errors['subject'] = 'Subject line is required'
  } else if (template.subject.length > 255) {
    errors['subject'] = 'Subject line cannot exceed 255 characters'
  }

  if (template.description && template.description.length > 500) {
    errors['description'] = 'Description cannot exceed 500 characters'
  }

  if (template.preview_text && template.preview_text.length > 255) {
    errors['preview_text'] = 'Preview text cannot exceed 255 characters'
  }

  if (!template.category) {
    errors['category'] = 'Please select a template category'
  }

  if (!template.type) {
    errors['type'] = 'Please select a template type'
  }

  return errors
}

// Editor configuration
const emailEditorOptions = {
  minHeight: '500px',
  displayMode: 'email',
  appearance: {
    theme: isDark.value ? 'dark' : 'light',
    panels: {
      tools: {
        dock: 'right'
      }
    }
  },
  features: {
    previewDesktop: true,
    previewMobile: true,
    textEditor: {
      spellChecker: true,
      tables: true,
      cleanPaste: true,
    },
  },
  tools: {
    form: { enabled: false },
    countdown: { enabled: false },
  },
  mergeTags: {
    subscriber: {
      first_name: { name: 'First Name', value: '{{subscriber.first_name}}' },
      last_name: { name: 'Last Name', value: '{{subscriber.last_name}}' },
      email: { name: 'Email', value: '{{subscriber.email}}' },
      custom_fields: { name: 'Custom Fields', value: '{{subscriber.custom_fields}}' }
    },
    company: {
      name: { name: 'Company Name', value: '{{company.name}}' },
      address: { name: 'Company Address', value: '{{company.address}}' }
    },
    unsubscribe: {
      link: { name: 'Unsubscribe Link', value: '{{unsubscribe.link}}' }
    }
  }
}

// Computed properties
const filteredTemplates = computed(() => {
  let templates = props.templates || []

  if (activeCategory.value !== 'all') {
    templates = templates.filter(t => t.category === activeCategory.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    templates = templates.filter(t =>
      t.name.toLowerCase().includes(query) ||
      t.description.toLowerCase().includes(query) ||
      t.subject.toLowerCase().includes(query)
    )
  }

  return templates
})

// Event handlers
const onEditorLoaded = () => {
  if (tempTemplate.value?.design) {
    emailEditorRef.value.editor.loadDesign(JSON.parse(tempTemplate.design));
  }
}

const onEditorReady = () => {
  console.log('Editor ready')
}

const openTemplateEditor = () => {
  tempTemplate.value = {
    name: '',
    description: '',
    subject: '',
    preview_text: '',
    category: 'newsletter',
    is_default: false,
    variables: {},
    design: null
  }
  editorStep.value = 1
  isEditorOpen.value = true
}

const handleDetailsSubmit = () => {
  if (!tempTemplate.value.name || !tempTemplate.value.subject) {
    toast.error('Please fill in all required fields')
    return
  }

  editorStep.value = 2
}

const handleEditorBack = () => {
  editorStep.value = 1
}

const handleTemplateSelect = (template: EmailTemplate) => {
  selectedTemplate.value = template
  if (template.design && emailEditorRef.value) {
    emailEditorRef.value.editor.loadDesign(template.design)
  }
}

const handleCreateTemplate = () => {
  try {
    if (!emailEditorRef.value) {
      toast.error('Editor not ready')
      return
    }

    const validationErrors = validateTemplate(tempTemplate.value)

    if (Object.keys(validationErrors).length > 0) {
      errors.value = validationErrors
      const firstError = Object.values(validationErrors)[0]
      toast.error(firstError)
      return
    }

    isLoading.value = true
    errors.value = {}

    // Get both HTML and design
    emailEditorRef.value.editor.exportHtml((data: any) => {
      setTimeout(() => {

        const templateData = {
          ...tempTemplate.value,
          content: data.html,
          design: data.design
        }

        router.post(route('onboarding.update-step'), {
          step: 5,
          data: {
            template: templateData
          }
        }, {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('Template created successfully')
            isEditorOpen.value = false
            emit('next')
          },
          onError: (validationErrors) => {
            errors.value = validationErrors
            const firstError = Object.values(validationErrors)[0]
            toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
          }
        })

      }, 100)
    })


  } catch (error) {
    console.error('Failed to create template:', error)
    toast.error('Failed to create template')
  } finally {
    isLoading.value = false
  }
}

const handleNext = async () => {
  if (!selectedTemplate.value) {
    toast.error('Please select a template to continue')
    return
  }

  try {
    isLoading.value = true
    const { html, design } = await new Promise((resolve) => {
      emailEditorRef.value.editor.exportHtml((data: any) => {
        resolve(data)
      })
    })

    selectedTemplate.value.content = html
    selectedTemplate.value.design = design

    router.post(route('onboarding.update-step'), {
      step: 5,
      data: {
        template: selectedTemplate.value
      }
    }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Template selected successfully')
        emit('next')
      },
      onError: (validationErrors) => {
        errors.value = validationErrors
        const firstError = Object.values(validationErrors)[0]
        toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
      }
    })
  } catch (error) {
    console.error('Failed to save template:', error)
    toast.error('Failed to save template')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Choose Your Email Template</CardTitle>
      <CardDescription>
        Select from our pre-built templates or create your own custom template
      </CardDescription>
    </CardHeader>

    <CardContent class="space-y-6">
      <div class="flex items-center space-x-4">
        <div class="flex-1">
          <div class="relative">
            <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              class="pl-8"
              placeholder="Search templates..."
            />
          </div>
        </div>

        <Button @click="openTemplateEditor">
          <PlusIcon class="mr-2 h-4 w-4" />
          Create Template
        </Button>
      </div>

      <!-- Full-screen Template Editor -->
      <Sheet
        :open="isEditorOpen"
        @update:open="isEditorOpen = $event">
        <SheetContent
          class="w-screen h-screen p-0 flex flex-col"
          side="top">
          <SheetHeader class="p-6 border-b">
            <div class="flex items-center justify-between">
              <div>
                <SheetTitle>{{ editorStep === 1 ? 'Template Details' : 'Design Email Template' }}</SheetTitle>
                <SheetDescription>
                  {{ editorStep === 1 ? 'Enter basic information about your template' : 'Design your email using the visual editor' }}
                </SheetDescription>
              </div>

              <div class="flex items-center space-x-2">
                <Button
                  v-if="editorStep === 2"
                  variant="outline"
                  @click="handleEditorBack">
                  <ArrowLeftIcon class="mr-2 h-4 w-4" />
                  Back to Details
                </Button>

                <Button
                  v-if="editorStep === 1"
                  @click="handleDetailsSubmit"
                  :disabled="!tempTemplate.name || !tempTemplate.subject">
                  Continue to Editor
                  <ArrowRightIcon class="ml-2 h-4 w-4" />
                </Button>

                <Button
                  v-else
                  @click="handleCreateTemplate"
                  :disabled="isLoading">
                  {{ isLoading ? 'Saving...' : 'Save Template' }}
                </Button>
              </div>
            </div>
          </SheetHeader>

          <!-- Template Details Form -->
          <div
            v-if="editorStep === 1"
            class="flex-1 p-6 overflow-y-auto">
            <div class="max-w-2xl mx-auto space-y-6">
              <div class="space-y-4">
                <div>
                  <Label>Template Name <span class="text-destructive">*</span></Label>
                  <Input
                    v-model="tempTemplate.name"
                    placeholder="Monthly Newsletter"
                    :error="errors['data.template.name']"
                  />
                  <small class="text-destructive" v-if="errors['data.template.name']">
                    {{ errors['data.template.name'] }}
                  </small>
                </div>

                <div>
                  <Label>Category</Label>
                  <select
                    v-model="tempTemplate.category"
                    class="w-full rounded-md border border-input bg-background px-3 py-2">
                    <option value="newsletter">Newsletter</option>
                    <option value="promotional">Promotional</option>
                    <option value="transactional">Transactional</option>
                    <option value="notification">Notification</option>
                  </select>
                </div>

                <div>
                  <Label>Description</Label>
                  <Input
                    v-model="tempTemplate.description"
                    placeholder="A brief description of this template"
                    :error="errors['data.template.description']"
                  />
                </div>

                <div>
                  <Label>Subject Line <span class="text-destructive">*</span></Label>
                  <Input
                    v-model="tempTemplate.subject"
                    placeholder="Your Monthly Update from {company_name}"
                    :error="errors['data.template.subject']"
                  />
                </div>

                <div>
                  <Label>Preview Text</Label>
                  <Input
                    v-model="tempTemplate.preview_text"
                    placeholder="See what's new this month..."
                    :error="errors['data.template.preview_text']"
                  />
                </div>
              </div>

              <!-- Template Type Selection -->
              <div class="space-y-3">
                <Label>Template Type <span class="text-destructive">*</span></Label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div
                    v-for="type in templateTypes"
                    :key="type.value"
                    class="relative flex flex-col items-start p-4 rounded-lg border-2 cursor-pointer transition-colors"
                    :class="{
                      'border-primary bg-primary/5': tempTemplate.type === type.value,
                      'border-border hover:border-primary/50': tempTemplate.type !== type.value
                    }"
                    @click="handleTypeChange(type.value)">
                    <div class="flex items-center justify-between w-full">
                      <component
                        :is="type.icon"
                        class="h-5 w-5"
                        :class="{
                          'text-primary': tempTemplate.type === type.value,
                          'text-muted-foreground': tempTemplate.type !== type.value
                        }"
                      />

                      <div
                        v-if="tempTemplate.type === type.value"
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-primary">
                        <CheckIcon class="h-3 w-3 text-primary-foreground" />
                      </div>
                    </div>

                    <h3 class="mt-4 font-medium" :class="{
                        'text-primary': tempTemplate.type === type.value
                      }">
                      {{ type.label }}
                    </h3>

                    <p class="mt-1 text-sm text-muted-foreground">
                      {{ type.description }}
                    </p>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Email Editor -->
          <div
            v-else
            class="flex-1 h-[calc(100vh-120px)]">
            <EmailEditor
              ref="emailEditorRef"
              v-if="showTemplateEditor"
              :options="emailEditorOptions"
              :project-id="Number(projectId)"
              @load="onEditorLoaded"
              @ready="onEditorReady"
              class="h-full w-full"
            />

            <!-- HTML Editor -->
            <div v-else-if="showHtmlEditor" class="h-full">
              <MonacoEditor
                v-model="tempTemplate.content"
                language="html"
                :options="{
                  theme: isDark ? 'vs-dark' : 'vs',
                  minimap: { enabled: false },
                  lineNumbers: 'on',
                  fontSize: 14,
                }"
              />
            </div>

            <!-- Markdown Editor -->
            <div v-else-if="showMarkdownEditor" class="h-full">
              <MonacoEditor
                v-model="tempTemplate.content"
                language="markdown"
                :options="{
                  theme: isDark ? 'vs-dark' : 'vs',
                  minimap: { enabled: false },
                  lineNumbers: 'on',
                  fontSize: 14,
                  wordWrap: 'on'
                }"
              />
            </div>
          </div>
        </SheetContent>
      </Sheet>

      <ScrollArea class="h-[400px] rounded-md border p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div
            v-for="template in filteredTemplates"
            class="relative rounded-lg border p-4 cursor-pointer hover:border-primary transition-colors"
            :class="{ 'border-primary': selectedTemplate?.id === template.id }"
            @click="handleTemplateSelect(template)"
            :key="template.id">
            <div class="absolute top-2 right-2" v-if="selectedTemplate?.id === template.id">
              <CheckIcon class="h-4 w-4 text-primary" />
            </div>

            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <h3 class="font-medium">{{ template.name }}</h3>
                <span v-if="template.is_default" class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                  Default
                </span>
              </div>

              <p class="text-sm text-muted-foreground line-clamp-2">
                {{ template.description }}
              </p>

              <p class="text-sm">{{ template.subject }}</p>

              <div class="flex items-center space-x-2">
                <span class="text-xs px-2 py-1 rounded-full bg-muted">
                  {{ template.category }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="filteredTemplates.length === 0"
          class="flex flex-col items-center justify-center h-full py-8">
          <p class="text-muted-foreground">No templates found</p>
        </div>
      </ScrollArea>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="$emit('back')">
          <ArrowLeftIcon class="mr-2 h-4 w-4" />
          Back
        </Button>

        <Button
          @click="handleNext"
          :disabled="!selectedTemplate || isLoading">
          {{ isLoading ? 'Processing...' : 'Continue' }}
          <ArrowRightIcon
            v-if="!isLoading"
            class="ml-2 h-4 w-4"
          />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<style lang="scss">
/* Editor Styles */
.unlayer-wrapper {
  height: 100% !important;
  min-height: 500px;
  background-color: var(--background);
}

.unlayer-editor {
  height: 100% !important;
}

.unlayer-editor-window {
  width: 100% !important;
}

/* Hide unlayer branding */
.blockbuilder-branding {
  display: none !important;
}

/* Dark theme adjustments */
.dark {
  .unlayer-wrapper {
    background: var(--background);
  }

  .unlayer-sidebar {
    background: var(--card);
    border-color: var(--border);
  }

  .unlayer-toolbar {
    background: var(--card);
    border-color: var(--border);
  }

  .unlayer-property-editor {
    background: var(--card);
    border-color: var(--border);
  }

  .unlayer-context-menu {
    background: var(--card);
    border-color: var(--border);
  }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .unlayer-wrapper {
    min-height: 400px;
  }

  .unlayer-toolbar {
    flex-direction: column;
  }
}

.editor-type-card {
  @apply relative flex flex-col items-start p-4 rounded-lg border-2 cursor-pointer transition-colors;

  &:hover {
    @apply border-primary/50;
  }

  &.selected {
    @apply border-primary bg-primary/5;

    .editor-type-icon {
      @apply text-primary;
    }

    .editor-type-title {
      @apply text-primary;
    }
  }
}

/* Monaco editor styles */
.monaco-editor {
  .editor-container {
    @apply h-full;
  }
}
</style>

