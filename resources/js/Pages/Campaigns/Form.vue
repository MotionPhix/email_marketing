<script setup>
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EmailEditor from '@/Components/Campaign/EmailEditor.vue'
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/Components/ui/form'
import { Input } from '@/Components/ui/input'
import { useToast } from '@/Components/ui/toast/use-toast'
import Combobox from "@/Components/Combobox.vue";

const { toast } = useToast()

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

const form = useForm({
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
})

const isEditing = computed(() => !!props.campaign.id)
const editor = ref(null)

const personalizations = {
  subscriber: {
    label: 'Subscriber',
    fields: [
      { key: 'first_name', label: 'First Name' },
      { key: 'last_name', label: 'Last Name' },
      { key: 'email', label: 'Email' },
      { key: 'unsubscribe_url', label: 'Unsubscribe Link' }
    ]
  },
  campaign: {
    label: 'Campaign',
    fields: [
      { key: 'name', label: 'Name' },
      { key: 'subject', label: 'Subject' },
      { key: 'web_view_url', label: 'Web View Link' }
    ]
  }
}

const templateOptions = computed(() =>
  props.templates.map(template => ({
    value: template.id,
    label: template.name
  }))
)

const onSave = (isDraft = true) => {
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
      }
    })
  })
}

const onSchedule = () => {
  if (!form.settings.scheduled_at) {
    toast({
      variant: 'destructive',
      title: 'Error',
      description: 'Please select a schedule date and time'
    })
    return
  }

  form.post(route('campaigns.schedule', props.campaign.id), {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: 'Campaign scheduled successfully'
      })
    }
  })
}

</script>

<template>
  <AppLayout :title="isEditing ? 'Edit Campaign' : 'Create Campaign'">
    <Head :title="isEditing ? 'Edit Campaign' : 'Create Campaign'" />

    <div class="space-y-6 p-6">
      <!-- Campaign Details -->
      <Card>
        <CardHeader>
          <CardTitle>Campaign Details</CardTitle>
          <CardDescription>
            Basic information about your email campaign
          </CardDescription>
        </CardHeader>

        <CardContent>
          <Form :form="form" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <FormField
                v-slot="{ field }"
                :name="name">
                <FormItem>
                  <FormLabel>Campaign Name</FormLabel>
                  <FormControl>
                    <Input v-bind="field" placeholder="Spring Newsletter 2024" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField
                v-slot="{ field }"
                :name="subject">
                <FormItem>
                  <FormLabel>Email Subject</FormLabel>
                  <FormControl>
                    <Input v-bind="field" placeholder="Your Spring Updates Are Here!" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <FormField
                v-slot="{ field }"
                :name="from_name">
                <FormItem>
                  <FormLabel>From Name</FormLabel>
                  <FormControl>
                    <Input v-bind="field" placeholder="Your Company Name" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField
                v-slot="{ field }"
                :name="from_email">
                <FormItem>
                  <FormLabel>From Email</FormLabel>
                  <FormControl>
                    <Input
                      v-bind="field"
                      placeholder="newsletter@yourcompany.com"
                      type="email" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>
            </div>

            <FormField
              v-slot="{ field }"
              :name="reply_to">
              <FormItem>
                <FormLabel>Reply-To Email (Optional)</FormLabel>
                <FormControl>
                  <Input
                    v-bind="field"
                    placeholder="support@yourcompany.com"
                    type="email" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>

            <FormField
              v-slot="{ field }"
              :name="template_id">
              <FormItem>
                <FormLabel>Email Template</FormLabel>
                <FormControl>
                  <Combobox
                    v-model="form.template_id"
                    :options="templateOptions"
                    placeholder="Select a template"
                    search-placeholder="Search templates..."
                    empty-message="No templates found"
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>
          </Form>
        </CardContent>
      </Card>

      <!-- Email Editor -->
      <Card>
        <CardHeader>
          <CardTitle>Email Content</CardTitle>
          <CardDescription>
            Design your email using the visual editor
          </CardDescription>
        </CardHeader>

        <CardContent>
          <EmailEditor
            ref="editor"
            v-model="campaign.content"
            :initial-design="form.content ? JSON.parse(form.content) : null"
            :personalization="personalizations"
            class="h-[600px] w-full"
          />
        </CardContent>
      </Card>

      <!-- Campaign Settings -->
      <Card>
        <CardHeader>
          <CardTitle>Campaign Settings</CardTitle>
          <CardDescription>
            Configure tracking and scheduling options
          </CardDescription>
        </CardHeader>

        <CardContent>
          <Form :form="form" class="space-y-4">
            <div class="flex items-center space-x-4">
              <FormField
                v-slot="{ field }"
                name="settings.track_opens">
                <FormItem class="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox v-bind="field" />
                  </FormControl>
                  <FormLabel>Track Opens</FormLabel>
                </FormItem>
              </FormField>

              <FormField
                v-slot="{ field }"
                name="settings.track_clicks">
                <FormItem class="flex items-center space-x-2">
                  <FormControl>
                    <Checkbox v-bind="field" />
                  </FormControl>
                  <FormLabel>Track Clicks</FormLabel>
                </FormItem>
              </FormField>
            </div>

            <FormField
              v-slot="{ field }"
              name="settings.schedule_send">
              <FormItem class="flex items-center space-x-2">
                <FormControl>
                  <Checkbox v-bind="field" />
                </FormControl>
                <FormLabel>Schedule Send</FormLabel>
              </FormItem>
            </FormField>

            <div v-if="form.settings.schedule_send" class="grid grid-cols-2 gap-4">
              <FormField
                v-slot="{ field }"
                name="settings.scheduled_at">
                <FormItem>
                  <FormLabel>Schedule Date & Time</FormLabel>
                  <FormControl>
                    <Input
                      v-bind="field"
                      type="datetime-local"
                      :min="new Date().toISOString().slice(0, 16)"
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField
                v-slot="{ field }"
                name="settings.timezone">
                <FormItem>
                  <FormLabel>Timezone</FormLabel>
                  <FormControl>
                    <Select v-bind="field">
                      <option v-for="tz in Intl.supportedValuesOf('timeZone')"
                              :key="tz"
                              :value="tz">
                        {{ tz }}
                      </option>
                    </Select>
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>
            </div>
          </Form>
        </CardContent>
      </Card>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-4">
        <Button
          variant="outline"
          :disabled="form.processing"
          @click="onSave(true)">
          Save as Draft
        </Button>

        <Button
          v-if="form.settings.schedule_send"
          variant="default"
          :disabled="form.processing"
          @click="onSchedule">
          Schedule Campaign
        </Button>

        <Button
          v-else
          variant="default"
          :disabled="form.processing"
          @click="onSave(false)">
          {{ isEditing ? 'Update' : 'Create' }} Campaign
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
