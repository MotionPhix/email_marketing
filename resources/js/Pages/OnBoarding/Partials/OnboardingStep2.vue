<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

const props = defineProps<{
  formData?: {
    sender_settings?: {
      default_sender_name: string
      default_sender_email: string
    }
    email_settings?: {
      from_name: string
      reply_to: string
    }
    preferences?: {
      language: string
      timezone: string
    }
  }
  disabledBack?: boolean
}>()

const emit = defineEmits(['back', 'next'])

const form = useForm({
  sender_settings: {
    default_sender_name: props.formData?.sender_settings?.default_sender_name || '',
    default_sender_email: props.formData?.sender_settings?.default_sender_email || '',
  },
  email_settings: {
    from_name: props.formData?.email_settings?.from_name || '',
    reply_to: props.formData?.email_settings?.reply_to || '',
  },
  preferences: {
    language: props.formData?.preferences?.language || 'en',
    timezone: props.formData?.preferences?.timezone || 'UTC',
  }
})

const timezones = [
  { value: 'UTC', label: 'UTC' },
  { value: 'America/New_York', label: 'Eastern Time (ET)' },
  { value: 'America/Chicago', label: 'Central Time (CT)' },
  { value: 'America/Denver', label: 'Mountain Time (MT)' },
  { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
]

const languages = [
  { value: 'en', label: 'English' },
  { value: 'es', label: 'Spanish' },
  { value: 'fr', label: 'French' },
]

const handleSubmit = () => {
  form
    .transform(data => {
      return {
        step: 2,
        data: data
      }
    })
    .post(route('onboarding.update-step'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Account settings saved successfully')
      emit('next')
    },
    onError: (errors) => {
      Object.keys(errors).map(k => {
        toast.error(errors[k])
      })
    }
  })
}
</script>

<template>
  <Card>
    <CardContent class="p-6 space-y-6">
      <div class="space-y-4">
        <h3 class="text-lg font-semibold">Email Sender Settings</h3>
        <p class="text-sm text-muted-foreground">
          These settings will be used as defaults when sending emails to your subscribers.
        </p>

        <div class="space-y-4">
          <div>
            <FormField
              label="Default Sender Name"
              v-model="form.sender_settings.default_sender_name"
              placeholder="John from Company"
              :error="form.errors['data.sender_settings.default_sender_name']"
            />
          </div>

          <div>
            <FormField
              label="Default Sender Email"
              type="email"
              v-model="form.sender_settings.default_sender_email"
              placeholder="newsletters@yourcompany.com"
              :error="form.errors['data.sender_settings.default_sender_email']"
            />
          </div>

          <div>
            <FormField
              label="From Name"
              v-model="form.email_settings.from_name"
              placeholder="Marketing Team"
              :error="form.errors['data.email_settings.from_name']"
            />
          </div>

          <div>
            <FormField
              label="Reply-To Email"
              type="email"
              v-model="form.email_settings.reply_to"
              placeholder="support@yourcompany.com"
              :error="form.errors['data.email_settings.reply_to']"
            />
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <h3 class="text-lg font-semibold">Preferences</h3>
        <p class="text-sm text-muted-foreground">
          Set your preferred language and timezone for better experience.
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <FormField
              label="Language"
              v-model="form.preferences.language"
              :error="form.errors['data.preferences.language']"
              placeholder="Select language"
              :options="languages"
              type="select"
            />
          </div>

          <div>
            <FormField
              label="Timezone"
              v-model="form.preferences.timezone"
              :error="form.errors['data.preferences.timezone']"
              placeholder="Select timezone"
              :options="timezones"
              type="select"
            />
          </div>
        </div>
      </div>
    </CardContent>

    <CardFooter class="flex justify-between">
      <Button
        variant="outline"
        @click="emit('back')"
        :disabled="props.disabledBack">
        Back
      </Button>

      <Button
        type="submit"
        @click="handleSubmit"
        :loading="form.processing"
      >
        Continue
      </Button>
    </CardFooter>
  </Card>
</template>
