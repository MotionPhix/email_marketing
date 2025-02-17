<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Card, CardContent, CardFooter } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'

const props = defineProps<{
  formData?: any
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
  form.post(route('onboarding.update-step'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Account settings saved successfully')
      emit('next')
    },
    onError: (errors) => {
      toast.error('Please fix the errors below')
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
            <Label for="default_sender_name">Default Sender Name</Label>
            <Input
              id="default_sender_name"
              v-model="form.sender_settings.default_sender_name"
              placeholder="John from Company"
              :error="form.errors['sender_settings.default_sender_name']"
            />
          </div>

          <div>
            <Label for="default_sender_email">Default Sender Email</Label>
            <Input
              id="default_sender_email"
              type="email"
              v-model="form.sender_settings.default_sender_email"
              placeholder="newsletters@yourcompany.com"
              :error="form.errors['sender_settings.default_sender_email']"
            />
          </div>

          <div>
            <Label for="from_name">From Name</Label>
            <Input
              id="from_name"
              v-model="form.email_settings.from_name"
              placeholder="Marketing Team"
              :error="form.errors['email_settings.from_name']"
            />
          </div>

          <div>
            <Label for="reply_to">Reply-To Email</Label>
            <Input
              id="reply_to"
              type="email"
              v-model="form.email_settings.reply_to"
              placeholder="support@yourcompany.com"
              :error="form.errors['email_settings.reply_to']"
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
            <Label for="language">Language</Label>
            <Select
              v-model="form.preferences.language"
              :error="form.errors['preferences.language']"
            >
              <SelectTrigger>
                <SelectValue placeholder="Select language" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="lang in languages"
                  :key="lang.value"
                  :value="lang.value"
                >
                  {{ lang.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="timezone">Timezone</Label>
            <Select
              v-model="form.preferences.timezone"
              :error="form.errors['preferences.timezone']"
            >
              <SelectTrigger>
                <SelectValue placeholder="Select timezone" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="tz in timezones"
                  :key="tz.value"
                  :value="tz.value"
                >
                  {{ tz.label }}
                </SelectItem>
              </SelectContent>
            </Select>
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
