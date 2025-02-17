<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import {
  ArrowLeftIcon,
  ArrowRightIcon,
  PlusIcon,
  SearchIcon,
  CheckIcon
} from 'lucide-vue-next'
import { ScrollArea } from '@/Components/ui/scroll-area'
import EmailEditor from "@/Components/Campaign/EmailEditor.vue";

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
const activeCategory = ref('all')

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
  variables: {}
} as EmailTemplate)
const errors = ref({})
const isLoading = ref(false)

const categories = [
  { value: 'all', label: 'All Templates' },
  { value: 'newsletter', label: 'Newsletters' },
  { value: 'promotional', label: 'Promotional' },
  { value: 'transactional', label: 'Transactional' },
  { value: 'notification', label: 'Notifications' }
]

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
}

const handleCreateTemplate = () => {
  isLoading.value = true
  errors.value = {}

  router.post(route('onboarding.update-step'), {
    step: 5,
    data: {
      template: newTemplate.value
    }
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Template created successfully')
      isCreatingTemplate.value = false
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
}

const handleNext = () => {
  if (!selectedTemplate.value) {
    toast.error('Please select a template to continue')
    return
  }

  isLoading.value = true
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

        <Dialog v-model:open="isCreatingTemplate">
          <DialogTrigger asChild>
            <Button>
              <PlusIcon class="mr-2 h-4 w-4" />
              Create Template
            </Button>
          </DialogTrigger>

          <DialogContent class="max-w-4xl">
            <DialogHeader>
              <DialogTitle>Create New Template</DialogTitle>
              <DialogDescription>
                Design your custom email template with our editor
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-6 py-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label>Template Name</Label>
                  <Input
                    v-model="newTemplate.name"
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
                    v-model="newTemplate.category"
                    class="w-full rounded-md border border-input bg-background px-3 py-2"
                  >
                    <option value="newsletter">Newsletter</option>
                    <option value="promotional">Promotional</option>
                    <option value="transactional">Transactional</option>
                    <option value="notification">Notification</option>
                  </select>
                </div>
              </div>

              <div>
                <Label>Description</Label>
                <Input
                  v-model="newTemplate.description"
                  placeholder="A brief description of this template"
                  :error="errors['data.template.description']"
                />
              </div>

              <div>
                <Label>Subject Line</Label>
                <Input
                  v-model="newTemplate.subject"
                  placeholder="Your Monthly Update from {company_name}"
                  :error="errors['data.template.subject']"
                />
                <small class="text-destructive" v-if="errors['data.template.subject']">
                  {{ errors['data.template.subject'] }}
                </small>
              </div>

              <div>
                <Label>Preview Text</Label>
                <Input
                  v-model="newTemplate.preview_text"
                  placeholder="See what's new this month..."
                  :error="errors['data.template.preview_text']"
                />
              </div>

              <div>
                <Label>Content</Label>
                <EmailEditor
                  v-model="newTemplate.content"
                  api-key="your-tinymce-api-key"
                  :init="{
                    height: 400,
                    menubar: true,
                    plugins: [
                      'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                      'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                      'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                      'bold italic forecolor | alignleft aligncenter ' +
                      'alignright alignjustify | bullist numlist outdent indent | ' +
                      'removeformat | help'
                  }"
                />
                <small class="text-destructive" v-if="errors['data.template.content']">
                  {{ errors['data.template.content'] }}
                </small>
              </div>

              <div>
                <Label>Template Variables</Label>
                <p class="text-sm text-muted-foreground mb-2">
                  Available variables: {first_name}, {last_name}, {company_name}, {unsubscribe_link}
                </p>
              </div>
            </div>

            <div class="flex justify-end space-x-4">
              <Button
                variant="outline"
                @click="isCreatingTemplate = false"
              >
                Cancel
              </Button>
              <Button
                @click="handleCreateTemplate"
                :disabled="isLoading"
              >
                {{ isLoading ? 'Creating...' : 'Create Template' }}
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <Tabs v-model="activeCategory" class="w-full">
        <TabsList class="grid w-full grid-cols-5">
          <TabsTrigger
            v-for="category in categories"
            :key="category.value"
            :value="category.value"
          >
            {{ category.label }}
          </TabsTrigger>
        </TabsList>
      </Tabs>

      <ScrollArea class="h-[400px] rounded-md border p-4">
        <div class="grid grid-cols-2 gap-4">
          <div
            v-for="template in filteredTemplates"
            :key="template.id"
            class="relative rounded-lg border p-4 cursor-pointer hover:border-primary transition-colors"
            :class="{ 'border-primary': selectedTemplate?.id === template.id }"
            @click="handleTemplateSelect(template)"
          >
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
          <ArrowLeftIcon class="mr-2 h-4 w-4" />
          Back
        </Button>

        <Button
          @click="handleNext"
          :disabled="!selectedTemplate || isLoading"
        >
          {{ isLoading ? 'Processing...' : 'Continue' }}
          <ArrowRightIcon v-if="!isLoading" class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
