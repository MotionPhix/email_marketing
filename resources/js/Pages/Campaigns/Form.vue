<script setup lang="ts">
import {ref, computed, markRaw} from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useForm as useVeeForm } from 'vee-validate'
import * as yup from 'yup'
import AppLayout from '@/Layouts/AppLayout.vue'
import Steps from '@/Components/Campaign/Steps/Step.vue'
import DetailStep from '@/Components/Campaign/Steps/DetailStep.vue'
import EditorStep from '@/Components/Campaign/Steps/EditorStep.vue'
import StepContainer from '@/Components/Campaign/Steps/StepContainer.vue'
import { toast } from 'vue-sonner'
import { useStorage } from '@vueuse/core'
import {Campaign, EmailTemplate} from "@/types";

interface Props {
  campaign: Campaign
  templates: EmailTemplate[]
  userSettings: {
    from_name: string
    from_email: string
    reply_to?: string
  }
}

const props = defineProps<Props>()

// Define validation schema
const validationSchema = yup.object({
  name: yup.string().required('Campaign name is required').max(255),
  subject: yup.string().required('Email subject is required').max(255),
  from_name: yup.string().required('From name is required').max(255),
  from_email: yup.string().required('From email is required').email().max(255),
  reply_to: yup.string().email().nullable(),
  content: yup.string().required('Email content is required'),
  template_id: yup.number().nullable(),
  recipients: yup.array().min(1, 'At least one recipient list is required'),
  settings: yup.object({
    track_opens: yup.boolean(),
    track_clicks: yup.boolean(),
    schedule_send: yup.boolean(),
    scheduled_at: yup.date().nullable().when('schedule_send', {
      is: true,
      then: (schema) => schema.required('Schedule date is required')
        .min(new Date(), 'Schedule date must be in the future')
    }),
    timezone: yup.string().required('Timezone is required')
  })
})

// Initialize vee-validate form
const { handleSubmit, values, errors, setFieldValue } = useVeeForm({
  validationSchema,
  initialValues: {
    name: props.campaign.name || '',
    subject: props.campaign.subject || '',
    from_name: props.campaign.from_name || props.userSettings.from_name,
    from_email: props.campaign.from_email || props.userSettings.from_email,
    reply_to: props.campaign.reply_to || props.userSettings.reply_to || '',
    content: props.campaign.content || '',
    template_id: props.campaign.template_id || null,
    recipients: props.campaign.recipients || [],
    settings: {
      track_opens: true,
      track_clicks: true,
      schedule_send: false,
      scheduled_at: props.campaign.settings?.scheduled_at || null,
      timezone: props.campaign.settings?.timezone ||
        Intl.DateTimeFormat().resolvedOptions().timeZone
    }
  }
})

// Initialize Inertia form
const form = useForm(values)
const isEditing = computed(() => !!props.campaign.id)
const currentStep = useStorage('campaign_form_step', 1)
const editor = ref<InstanceType<typeof EditorStep> | null>(null)
const lastSaved = useStorage('campaign_last_saved', '')

// Custom merge tags
const mergeTags = {
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
    },
  },
  campaign: {
    name: 'Campaign',
    mergeTags: {
      subject: {
        name: 'Subject',
        value: '{{campaign.subject}}',
      },
      unsubscribe_url: {
        name: 'Unsubscribe Link',
        value: '{{campaign.unsubscribe_url}}',
      },
    },
  },
}

// Step components configuration
const stepComponents = {
  1: markRaw(DetailStep),
  2: markRaw(EditorStep)
}

const stepProps = computed(() => ({
  1: {
    form,
    errors,
    templates: props.templates,
    processing: form.processing,
    onNext: handleNext
  },
  2: {
    modelValue: form.content,
    initialContent: props.campaign.content,
    processing: form.processing,
    isSaving: form.processing,
    lastSaved: lastSaved.value,
    onBack: handleBack,
    onSave: handleSave,
    'onUpdate:modelValue': (value: string) => form.content = value,
    'onContent:change': handleDesignUpdated
  }
}))

const currentStepComponent = computed(() =>
  stepComponents[currentStep.value as keyof typeof stepComponents]
)

const currentStepProps = computed(() =>
  stepProps.value[currentStep.value as keyof typeof stepProps]
)

// Steps array for progress indicator
const steps = [
  {
    id: 1,
    name: 'Campaign Details',
    description: 'Set up your campaign information',
    completed: computed(() => currentStep.value > 1)
  },
  {
    id: 2,
    name: 'Email Design',
    description: 'Design your email content',
    completed: computed(() => false)
  }
]

// Navigation handlers
const handleNext = async () => {
  const isValid = await handleSubmit(() => {
    currentStep.value++
    return true
  })()

  if (!isValid) {
    toast.error('Please fix the validation errors before continuing')
  }
}

const handleBack = () => {
  currentStep.value--
}

// Save handlers
const handleSave = async (isDraft = true) => {
  try {
    if (!editor.value) return

    const design = await editor.value.saveDesign()
    form.content = JSON.stringify(design)

    const action = isEditing.value ? 'put' : 'post'
    const url = isEditing.value
      ? route('campaigns.update', props.campaign.id)
      : route('campaigns.store')

    form[action](url, {
      onSuccess: () => {
        lastSaved.value = new Date().toISOString()
        toast.success(
          `Campaign ${isEditing.value ? 'updated' : 'created'} successfully`
        )
      },
      onError: (errors) => {
        toast.error('Failed to save campaign')
        console.error(errors)
      }
    })
  } catch (error) {
    toast.error('Failed to save email design')
    console.error(error)
  }
}

const handleDesignUpdated = () => {
  // Auto-save functionality could be implemented here
  console.log('Design updated')
}

const handleSchedule = () => {
  if (!form.settings.scheduled_at) {
    toast.error('Please set a schedule date')
    return
  }

  form.post(route('campaigns.schedule', props.campaign.id), {
    onSuccess: () => {
      toast.success('Campaign scheduled successfully')
    },
    onError: () => {
      toast.error('Failed to schedule campaign')
    }
  })
}
</script>

<template>
  <AppLayout :title="isEditing ? 'Edit Campaign' : 'Create Campaign'">
    <Head :title="isEditing ? 'Edit Campaign' : 'Create Campaign'" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Progress Steps -->
      <Steps
        :steps="steps"
        :current-step="currentStep"
        class="mb-8"
      />

      <!-- Form Steps -->
      <form @submit.prevent>
        <KeepAlive>
          <StepContainer
            :is="currentStepComponent"
            v-bind="currentStepProps">
            <template
              v-if="currentStep === 2"
              #header>
              <!-- Editor Header -->
              <div class="h-16 border-b px-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <Button
                    variant="outline"
                    @click="handleBack">
                    Back
                  </Button>

                  <span
                    v-if="lastSaved"
                    class="text-sm text-muted-foreground">
                    Last saved {{ new Date(lastSaved).toLocaleTimeString() }}
                  </span>
                </div>

                <div class="flex items-center space-x-4">
                  <Button
                    variant="outline"
                    :disabled="form.processing"
                    @click="handleSave(true)">
                    Save Draft
                  </Button>

                  <Button
                    variant="default"
                    :disabled="form.processing"
                    @click="handleSave(false)">
                    Save & Continue
                  </Button>
                </div>
              </div>
            </template>
          </StepContainer>
        </KeepAlive>
      </form>
    </div>
  </AppLayout>
</template>
