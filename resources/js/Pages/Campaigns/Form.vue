<script setup lang="ts">
import {ref, computed} from 'vue'
import {Head, Link, useForm} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {Button} from '@/Components/ui/button'
import Steps from '@/Components/Campaign/Steps/Step.vue'
import DetailStep from '@/Components/Campaign/Steps/DetailStep.vue'
import EditorStep from '@/Components/Campaign/Steps/EditorStep.vue'
import StepContainer from '@/Components/Campaign/Steps/StepContainer.vue'
import {toast} from 'vue-sonner'
import type {Campaign, EmailTemplate} from '@/types'
import {IconPlus} from "@tabler/icons-vue";
import {useStorage} from "@vueuse/core";

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

// Initialize Inertia form
const form = useForm({
  id: props.campaign?.id || null,
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
  },
  current_step: props.campaign.current_step || 1, // Add this line
})

const isEditing = computed(() => !!props.campaign.id)
const currentStep = computed(() => form.current_step)
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
  1: DetailStep,
  2: EditorStep
}

const stepProps = computed(() => ({
  1: {
    form,
    templates: props.templates,
    processing: form.processing,
    onNext: handleNext
  },
  2: {
    modelValue: form.content,
    initialContent: props.campaign.content,
    mergeTags,
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
const handleNext = () => {

  if (props.campaign.id) {

    // Save first step data and create draft campaign
    form.put(route('campaigns.update', props.campaign.id), {
      preserveScroll: true,
      onSuccess: (response) => {
        // Update form with returned campaign data (to get the ID)
        if (response?.props?.campaign) {
          form.id = response.props.campaign.id
        }
        form.current_step = 2 // Set the next step
        toast.success('Campaign details saved')
      },
      onError: () => {
        form.current_step = 1
        toast.error('Please fix the validation errors before continuing')
      }
    })

  } else {

    // Save first step data and create draft campaign
    form.post(route('campaigns.draft'), {
      preserveScroll: true,
      onSuccess: (response) => {
        // Update form with returned campaign data (to get the ID)
        if (response?.props?.campaign) {
          form.id = response.props.campaign.id
        }
        form.current_step = 2 // Set the next step
        toast.success('Campaign details saved')
      },
      onError: () => {
        form.current_step = 1
        toast.error('Please fix the validation errors before continuing')
      }
    })

  }
}

const handleBack = () => {
  form.current_step = 1
}

// Save handlers
/*const handleSave = async (isDraft = true) => {
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
      onError: () => {
        toast.error('Failed to save campaign')
      }
    })
  } catch (error) {
    toast.error('Failed to save email design')
    console.error(error)
  }
}*/

// Modify handleSave to update existing draft
const handleSave = async (isDraft = true) => {
  try {
    if (!editor.value) return

    const design = await editor.value.saveDesign()
    form.content = JSON.stringify(design)

    // Always use PUT since we're updating the draft
    form.put(route('campaigns.update', form.id), {
      onSuccess: () => {
        lastSaved.value = new Date().toISOString()

        toast.success(
          `Campaign ${isDraft ? 'saved as draft' : 'created'} successfully`
        )
      },
      onError: () => {
        toast.error('Failed to save campaign')
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
  <AppLayout
    :title="isEditing ? 'Edit Campaign' : 'Create Campaign'">
    <Head :title="isEditing ? 'Edit Campaign' : 'Create Campaign'"/>

    <template #action>
      <Button asChild>
        <Link :href="route('campaigns.create')">
          <IconPlus class="mr-2 h-4 w-4"/>
          New Campaign
        </Link>
      </Button>
    </template>

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
