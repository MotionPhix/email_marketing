<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useForm as useVeeForm, Field } from 'vee-validate'
import * as yup from 'yup'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Form } from '@/Components/ui/form'
import Steps from '@/Components/Campaign/Steps/Step.vue'
import DetailsStep from '@/Components/Campaign/Steps/Detail.vue'
import EditorStep from '@/Components/Campaign/Steps/Editor.vue'
import {toast} from "vue-sonner";
import {useStorage} from "@vueuse/core";

const props = defineProps({
  campaign: {
    type: Object,
    required: true
  },
  templates: {
    type: Array,
    required: true
  }
})

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
      then: (schema) => schema.required('Schedule date is required').min(new Date(), 'Schedule date must be in the future')
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
    from_name: props.campaign.from_name || '',
    from_email: props.campaign.from_email || '',
    reply_to: props.campaign.reply_to || '',
    content: props.campaign.content || '',
    template_id: props.campaign.template_id || null,
    recipients: props.campaign.recipients || [],
    settings: props.campaign.settings || {
      track_opens: true,
      track_clicks: true,
      schedule_send: false,
      scheduled_at: null,
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    }
  }
})

// Initialize Inertia form
const form = useForm(values)

const isEditing = computed(() => !!props.campaign.id)
const currentStep = useStorage('campaign_form_steps', 1)
const editor = ref(null)

const templateOptions = computed(() =>
  props.templates.map(template => ({
    value: template.id,
    label: template.name
  }))
)

const onSave = handleSubmit((values, { setErrors }) => {
  // Get editor content
  editor.value?.saveDesign().then(design => {
    form.content = JSON.stringify(design)

    const action = isEditing.value ? 'put' : 'post'
    const route = isEditing.value
      ? route('campaigns.update', props.campaign.id)
      : route('campaigns.store')

    form[action](route, {
      onSuccess: () => {
        toast({
          title: 'Success',
          description: `Campaign ${isEditing.value ? 'updated' : 'created'} successfully`
        })
      },
      onError: (errors) => {
        setErrors(errors)
      }
    })
  })
})

const onSchedule = handleSubmit((values, { setErrors }) => {
  if (!values.settings.scheduled_at) {
    setErrors({ 'settings.scheduled_at': 'Schedule date is required' })
    return
  }

  form.post(route('campaigns.schedule', props.campaign.id), {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: 'Campaign scheduled successfully'
      })
    },
    onError: (errors) => {
      setErrors(errors)
    }
  })
})

const steps = [
  {
    id: 1,
    name: 'Campaign Details',
    description: 'Set up your campaign information'
  },
  {
    id: 2,
    name: 'Email Design',
    description: 'Design your email content'
  }
]

const handleNext = () => {
  currentStep.value++
}

const handleBack = () => {
  currentStep.value--
}

const handleSave = ({ design, isDraft }) => {
  form.content = JSON.stringify(design)

  const action = isEditing.value ? 'put' : 'post'
  const route = isEditing.value
    ? route('campaigns.update', props.campaign.id)
    : route('campaigns.store')

  form[action](route, {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: `Campaign ${isEditing.value ? 'updated' : 'created'} successfully`
      })
    }
  })
}
</script>

<template>
  <AppLayout :title="isEditing ? 'Edit Campaign' : 'Create Campaign'">
    <Head :title="isEditing ? 'Edit Campaign' : 'Create Campaign'" />

    <div class="max-w-5xl mx-auto p-6 space-y-8">
      <Steps
        :steps="steps"
        :current-step="currentStep"
      />

      <Form @submit.prevent>
        <KeepAlive>
          <DetailsStep
            v-if="currentStep === 1"
            :form="form"
            :templates="templates"
            :processing="form.processing"
            @next="handleNext"
          />

          <EditorStep
            v-else
            :initial-content="form.content"
            :processing="form.processing"
            @back="handleBack"
            @save="handleSave"
          />
        </KeepAlive>
      </Form>
    </div>
  </AppLayout>
</template>
