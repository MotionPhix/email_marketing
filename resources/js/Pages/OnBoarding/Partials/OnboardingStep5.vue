<script setup lang="ts">
import {ref, computed} from 'vue'
import {router} from '@inertiajs/vue3'
import {toast} from 'vue-sonner'
import {EmailEditor} from 'vue-email-editor'
import {
  ArrowLeftIcon,
  ArrowRightIcon,
  PlusIcon,
  SearchIcon,
  CheckIcon
} from 'lucide-vue-next'
import {ScrollArea} from '@/Components/ui/scroll-area'
import ConditionalBlockEditor from "@/Components/Campaign/ConditionalBlockEditor.vue";
import {useStorage} from "@vueuse/core";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from '@/Components/ui/sheet'

interface EmailTemplate {
  id: number
  name: string
  description: string
  subject: string
  preview_text: string
  content: string
  category: string
  thumbnail?: string
  is_default: boolean
  variables?: Record<string, any>
  design?: any // For storing unlayer design JSON
}

const props = defineProps<{
  templates?: EmailTemplate[]
  formData?: {
    template?: EmailTemplate
  }
}>()

const emit = defineEmits(['back', 'next'])

// Template selection
const selectedTemplate = ref<EmailTemplate | null>(props.formData?.template || null)
const searchQuery = ref('')
const activeCategory = useStorage('template_category', 'all')
const emailEditorRef = ref()
const isEditorOpen = ref(false)
const editorStep = useStorage('editor_step', 1) // 1 = template details, 2 = email editor
const tempTemplate = ref<Partial<EmailTemplate>>({
  name: '',
  description: '',
  subject: '',
  preview_text: '',
  category: 'newsletter',
  is_default: false,
  variables: {},
  design: null
})


// New template form
const isCreatingTemplate = ref(false)
const newTemplate = ref({
  name: '',
  description: '',
  subject: '',
  preview_text: '',
  content: '',
  category: 'newsletter',
  is_default: false,
  variables: {},
  design: null
} as EmailTemplate)
const errors = ref({})
const isLoading = ref(false)

const categories = [
  {value: 'all', label: 'All Templates'},
  {value: 'newsletter', label: 'Newsletters'},
  {value: 'promotional', label: 'Promotional'},
  {value: 'transactional', label: 'Transactional'},
  {value: 'notification', label: 'Notifications'}
]

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
  // Validate template details
  if (!tempTemplate.value.name || !tempTemplate.value.subject) {
    toast.error('Please fill in all required fields')
    return
  }
  editorStep.value = 2
}

const handleEditorBack = () => {
  editorStep.value = 1
}

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

const handleTemplateSelect = (template: EmailTemplate) => {
  selectedTemplate.value = template
  if (template.design && emailEditorRef.value) {
    emailEditorRef.value.loadDesign(template.design)
  }
}

// Email Editor Configuration
const emailEditorOptions = {
  customCSS: [
    `.unlayer-wrapper { background-color: var(--background); }`,
    `.unlayer-content { color: var(--foreground); }`,
  ],
  features: {
    textEditor: {
      spellChecker: true,
      tables: true,
      cleanPaste: true,
    }
  },
  appearance: {
    theme: 'dark',
    panels: {
      tools: {
        dock: 'left'
      }
    }
  },
  tools: {
    custom: {
      title: 'Custom Elements',
      items: [
        {
          name: 'ConditionalBlock',
          label: 'Conditional',
          icon: 'fa-solid fa-code-branch',
          template: ConditionalBlockEditor
        }
      ]
    }
  },
  mergeTags: {
    "subscriber": {
      "first_name": {name: "First Name", value: "{{subscriber.first_name}}"},
      "last_name": {name: "Last Name", value: "{{subscriber.last_name}}"},
      "email": {name: "Email", value: "{{subscriber.email}}"},
      "custom_fields": {name: "Custom Fields", value: "{{subscriber.custom_fields}}"}
    },
    "company": {
      "name": {name: "Company Name", value: "{{company.name}}"},
      "address": {name: "Company Address", value: "{{company.address}}"}
    },
    "unsubscribe": {
      "link": {name: "Unsubscribe Link", value: "{{unsubscribe.link}}"}
    }
  }
}

const onEditorLoaded = (editor: any) => {
  console.log('Email editor loaded')
  if (selectedTemplate.value?.design) {
    editor.loadDesign(selectedTemplate.value.design)
  }
}

const onEditorReady = (editor: any) => {
  emailEditorRef.value = editor
}

const saveDesign = () => {
  return new Promise((resolve, reject) => {
    if (!emailEditorRef.value) {
      reject(new Error('Editor not ready'))
      return
    }

    emailEditorRef.value.saveDesign((design: any) => {
      resolve(design)
    })
  })
}

const exportHtml = () => {
  return new Promise((resolve, reject) => {
    if (!emailEditorRef.value) {
      reject(new Error('Editor not ready'))
      return
    }

    emailEditorRef.value.exportHtml((data: any) => {
      resolve({html: data.html, design: data.design})
    })
  })
}

const handleCreateTemplate = async () => {
  try {
    isLoading.value = true
    errors.value = {}

    const {html, design} = await exportHtml()
    const templateData = {
      ...tempTemplate.value,
      content: html,
      design: design
    }

    router.post(route('onboarding.updateStep'), {
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
      },
      onFinish: () => {
        isLoading.value = false
      }
    })
  } catch (error) {
    console.error('Failed to create template:', error)
    toast.error('Failed to create template')
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
    const {html, design} = await exportHtml()

    selectedTemplate.value.content = html
    selectedTemplate.value.design = design

    router.post(route('onboarding.updateStep'), {
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
      },
      onFinish: () => {
        isLoading.value = false
      }
    })
  } catch (error) {
    console.error('Failed to save template:', error)
    toast.error('Failed to save template')
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
            <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground"/>
            <Input
              v-model="searchQuery"
              class="pl-8"
              placeholder="Search templates..."
            />
          </div>
        </div>

        <Button @click="openTemplateEditor" class="flex items-center space-x-2">
          <PlusIcon class="mr-2 h-4 w-4"/>
          <span>Create Template</span>
        </Button>
      </div>

      <!-- Full-screen Template Editor -->
      <Sheet
        :open="isEditorOpen"
        @update:open="isEditorOpen = $event">
        <SheetContent
          class="w-screen p-0 flex flex-col" side="top">
          <SheetHeader class="p-6 border-b">
            <div class="flex items-center justify-between">
              <div>
                <SheetTitle>{{ editorStep === 1 ? 'Template Details' : 'Design Email Template' }}</SheetTitle>
                <SheetDescription>
                  {{
                    editorStep === 1 ? 'Enter basic information about your template' : 'Design your email using the visual editor'
                  }}
                </SheetDescription>
              </div>

              <div class="flex items-center space-x-2">
                <Button
                  v-if="editorStep === 2"
                  variant="outline"
                  @click="handleEditorBack"
                >
                  <ArrowLeftIcon class="mr-2 h-4 w-4"/>
                  Back to Details
                </Button>

                <Button
                  v-if="editorStep === 1"
                  @click="handleDetailsSubmit"
                  :disabled="!tempTemplate.name || !tempTemplate.subject"
                >
                  Continue to Editor
                  <ArrowRightIcon class="ml-2 h-4 w-4"/>
                </Button>

                <Button
                  v-else
                  @click="handleCreateTemplate"
                  :disabled="isLoading"
                >
                  {{ isLoading ? 'Saving...' : 'Save Template' }}
                </Button>
              </div>
            </div>
          </SheetHeader>

          <!-- Template Details Form -->
          <div
            v-if="editorStep === 1"
            class="flex-1 p-6 overflow-y-auto"
          >
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
                    class="w-full rounded-md border border-input bg-background px-3 py-2"
                  >
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
            </div>
          </div>

          <!-- Email Editor -->
          <div
            v-else
            id="example"
            class="flex-1 h-full bg-gray-100">
            <div class="container">
              <EmailEditor
                :options="emailEditorOptions"
                @loaded="onEditorLoaded"
                @ready="onEditorReady"
                style="height: 100%;"
              />
            </div>
          </div>
        </SheetContent>
      </Sheet>

      <ScrollArea class="h-[400px] rounded-md border p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div
            v-for="template in filteredTemplates"
            :key="template.id"
            class="relative rounded-lg border p-4 cursor-pointer hover:border-primary transition-colors"
            :class="{ 'border-primary': selectedTemplate?.id === template.id }"
            @click="handleTemplateSelect(template)"
          >
            <div class="absolute top-2 right-2" v-if="selectedTemplate?.id === template.id">
              <CheckIcon class="h-4 w-4 text-primary"/>
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
          class="flex flex-col items-center justify-center h-full py-8"
        >
          <p class="text-muted-foreground">No templates found</p>
        </div>
      </ScrollArea>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="$emit('back')"
        >
          <ArrowLeftIcon class="mr-2 h-4 w-4"/>
          Back
        </Button>

        <Button
          @click="handleNext"
          :disabled="!selectedTemplate || isLoading"
        >
          {{ isLoading ? 'Processing...' : 'Continue' }}
          <ArrowRightIcon v-if="!isLoading" class="ml-2 h-4 w-4"/>
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<style lang="scss">
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
}

#app, #example {
  height: 100% !important;
}

#example .container {
  display: flex;
  flex-direction: column;
  position: relative;
  @apply h-screen;
}

#bar {
  flex: 1;
  display: flex;
  max-height: 50px;
  @apply py-5;
}

a.blockbuilder-branding {
  display: none !important;
  @apply hidden;
}
</style>
