<script setup lang="ts">
import {ref, computed} from 'vue'
import {useForm} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {format} from 'date-fns'
import RecipientSelector from "@/Components/Campaign/RecipientSelector.vue";
import EmailEditor from "@/Components/Campaign/EmailEditor.vue";

interface Props {
  campaign?: {
    id: number
    name: string
    subject: string
    from_name: string
    from_email: string
    reply_to: string
    content: string
    template_id: string | null
    scheduled_at: string | null
    recipients: {
      segments: string[]
      lists: string[]
      excluded_lists: string[]
    }
    settings: {
      track_opens: boolean
      track_clicks: boolean
      schedule_timezone: string
    }
  }
  templates: Array<{
    id: string
    name: string
  }>
}

const props = withDefaults(defineProps<Props>(), {
  campaign: undefined,
})

const form = useForm({
  name: props.campaign?.name ?? '',
  subject: props.campaign?.subject ?? '',
  from_name: props.campaign?.from_name ?? '',
  from_email: props.campaign?.from_email ?? '',
  reply_to: props.campaign?.reply_to ?? '',
  content: props.campaign?.content ?? '',
  template_id: props.campaign?.template_id ?? null,
  scheduled_at: props.campaign?.scheduled_at ?? null,
  recipients: props.campaign?.recipients ?? {
    segments: [],
    lists: [],
    excluded_lists: [],
  },
  settings: props.campaign?.settings ?? {
    track_opens: true,
    track_clicks: true,
    schedule_timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
  },
})

const currentStep = ref(1)
const showScheduleModal = ref(false)

const steps = [
  {id: 1, name: 'Details', icon: 'file-text'},
  {id: 2, name: 'Content', icon: 'mail'},
  {id: 3, name: 'Recipients', icon: 'users'},
  {id: 4, name: 'Schedule', icon: 'calendar'},
  {id: 5, name: 'Review', icon: 'check-circle'},
]

const isEditing = computed(() => !!props.campaign)

const submit = () => {
  if (isEditing.value) {
    form.put(route('campaigns.update', props.campaign.id))
  } else {
    form.post(route('campaigns.store'))
  }
}

const saveDraft = () => {
  form.post(route('campaigns.drafts.store'))
}

const schedule = (date: Date) => {
  form.scheduled_at = date.toISOString()
  showScheduleModal.value = false
}
</script>

<template>
  <AppLayout :title="isEditing ? 'Edit Campaign' : 'Create Campaign'">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          {{ isEditing ? 'Edit Campaign' : 'Create Campaign' }}
        </h2>
        <div class="flex items-center space-x-4">
          <Button
            variant="outline"
            @click="saveDraft"
          >
            Save as Draft
          </Button>
          <Button
            variant="outline"
            @click="showScheduleModal = true"
          >
            Schedule
          </Button>
          <Button
            @click="submit"
            :disabled="form.processing"
          >
            {{ isEditing ? 'Update' : 'Create' }} Campaign
          </Button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Steps -->
        <nav aria-label="Progress" class="mb-8">
          <ol role="list" class="flex space-x-4">
            <li
              v-for="step in steps"
              :key="step.id"
              class="flex-1"
            >
              <button
                :class="[
                  'flex w-full items-center px-6 py-4 text-sm font-medium',
                  currentStep === step.id
                    ? 'border-b-2 border-primary text-primary'
                    : 'text-gray-500 hover:text-gray-700',
                ]"
                @click="currentStep = step.id"
              >
                <Icon :name="step.icon" class="mr-3 h-5 w-5"/>
                {{ step.name }}
              </button>
            </li>
          </ol>
        </nav>

        <!-- Form Steps -->
        <div class="space-y-8">
          <!-- Step 1: Details -->
          <div v-show="currentStep === 1">
            <Card>
              <CardHeader>
                <CardTitle>Campaign Details</CardTitle>
                <CardDescription>
                  Set up the basic information for your email campaign
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="grid gap-6">
                  <div class="grid gap-2">
                    <Label for="name">Campaign Name</Label>
                    <Input
                      id="name"
                      v-model="form.name"
                      placeholder="e.g., Monthly Newsletter"
                    />
                    <span
                      v-if="form.errors.name"
                      class="text-sm text-red-500"
                    >
                      {{ form.errors.name }}
                    </span>
                  </div>

                  <div class="grid gap-2">
                    <Label for="subject">Email Subject</Label>
                    <Input
                      id="subject"
                      v-model="form.subject"
                      placeholder="Enter subject line"
                    />
                    <span
                      v-if="form.errors.subject"
                      class="text-sm text-red-500">
                    {{ form.errors.subject }}
                    </span>
                  </div>

                  <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                      <Label for="from_name">From Name</Label>
                      <Input
                        id="from_name"
                        v-model="form.from_name"
                        placeholder="Sender name"
                      />
                      <span
                        v-if="form.errors.from_name"
                        class="text-sm text-red-500"
                      >
                        {{ form.errors.from_name }}
                      </span>
                    </div>

                    <div class="grid gap-2">
                      <Label for="from_email">From Email</Label>
                      <Input
                        id="from_email"
                        v-model="form.from_email"
                        type="email"
                        placeholder="sender@example.com"
                      />
                      <span
                        v-if="form.errors.from_email"
                        class="text-sm text-red-500"
                      >
                        {{ form.errors.from_email }}
                      </span>
                    </div>
                  </div>

                  <div class="grid gap-2">
                    <Label for="reply_to">Reply-To Email</Label>
                    <Input
                      id="reply_to"
                      v-model="form.reply_to"
                      type="email"
                      placeholder="replies@example.com"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Step 2: Content -->
          <div v-show="currentStep === 2">
            <Card>
              <CardHeader>
                <CardTitle>Email Content</CardTitle>
                <CardDescription>
                  Design your email using our editor or select a template
                </CardDescription>
              </CardHeader>

              <CardContent>
                <div class="space-y-6">
                  <!-- Template Selection -->
                  <div class="grid gap-2">
                    <Label>Start with a Template</Label>
                    <Select
                      v-model="form.template_id"
                      class="mb-6">
                      <option value="">Create from scratch</option>
                      <option
                        v-for="template in templates"
                        :key="template.id"
                        :value="template.id">
                        {{ template.name }}
                      </option>
                    </Select>
                  </div>

                  <!-- Email Editor -->
                  <div class="grid gap-2">
                    <Label>Email Content</Label>
                    <EmailEditor
                      v-model="form.content"
                      :variables="availableVariables"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Step 3: Recipients -->
          <div v-show="currentStep === 3">
            <Card>
              <CardHeader>
                <CardTitle>Select Recipients</CardTitle>
                <CardDescription>
                  Choose who will receive this campaign
                </CardDescription>
              </CardHeader>
              <CardContent>
                <RecipientSelector v-model="form.recipients"/>
              </CardContent>
            </Card>
          </div>

          <!-- Step 4: Schedule -->
          <div v-show="currentStep === 4">
            <Card>
              <CardHeader>
                <CardTitle>Campaign Schedule</CardTitle>
                <CardDescription>
                  Choose when to send your campaign
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-6">
                  <!-- Scheduling Options -->
                  <div class="flex items-center space-x-4">
                    <Button
                      variant="outline"
                      :class="{ 'bg-primary/10': !form.scheduled_at }"
                      @click="form.scheduled_at = null"
                    >
                      Send Immediately
                    </Button>
                    <Button
                      variant="outline"
                      :class="{ 'bg-primary/10': form.scheduled_at }"
                      @click="showScheduleModal = true"
                    >
                      Schedule for Later
                    </Button>
                  </div>

                  <!-- Selected Schedule -->
                  <div
                    v-if="form.scheduled_at"
                    class="rounded-lg border p-4"
                  >
                    <h4 class="font-medium">Scheduled Time</h4>
                    <p class="text-sm text-gray-500">
                      {{ format(new Date(form.scheduled_at), 'MMMM d, yyyy h:mm a') }}
                      ({{ form.settings.schedule_timezone }})
                    </p>
                  </div>

                  <!-- Tracking Options -->
                  <div class="space-y-4">
                    <h4 class="font-medium">Tracking Options</h4>
                    <div class="space-y-2">
                      <div class="flex items-center justify-between">
                        <Label>Track Opens</Label>
                        <Switch v-model="form.settings.track_opens"/>
                      </div>
                      <div class="flex items-center justify-between">
                        <Label>Track Clicks</Label>
                        <Switch v-model="form.settings.track_clicks"/>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Step 5: Review -->
          <div v-show="currentStep === 5">
            <Card>
              <CardHeader>
                <CardTitle>Review Campaign</CardTitle>
                <CardDescription>
                  Review your campaign details before sending
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-6">
                  <!-- Campaign Summary -->
                  <div class="rounded-lg border p-4">
                    <dl class="divide-y">
                      <div class="grid grid-cols-3 py-3">
                        <dt class="font-medium">Campaign Name</dt>
                        <dd class="col-span-2">{{ form.name }}</dd>
                      </div>
                      <div class="grid grid-cols-3 py-3">
                        <dt class="font-medium">Subject Line</dt>
                        <dd class="col-span-2">{{ form.subject }}</dd>
                      </div>
                      <div class="grid grid-cols-3 py-3">
                        <dt class="font-medium">From</dt>
                        <dd class="col-span-2">{{ form.from_name }} <{{ form.from_email }}></dd>
                      </div>
                      <div class="grid grid-cols-3 py-3">
                        <dt class="font-medium">Recipients</dt>
                        <dd class="col-span-2">
                          {{ recipientCount }} recipients selected
                        </dd>
                      </div>
                      <div class="grid grid-cols-3 py-3">
                        <dt class="font-medium">Schedule</dt>
                        <dd class="col-span-2">
                          {{
                            form.scheduled_at
                              ? format(new Date(form.scheduled_at), 'MMMM d, yyyy h:mm a')
                              : 'Send immediately'
                          }}
                        </dd>
                      </div>
                    </dl>
                  </div>

                  <!-- Preview Button -->
                  <div class="flex justify-center">
                    <Button
                      variant="outline"
                      @click="previewCampaign"
                    >
                      <Icon name="eye" class="mr-2 h-4 w-4"/>
                      Preview Campaign
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Navigation -->
        <div class="mt-8 flex items-center justify-between">
          <Button
            v-if="currentStep > 1"
            variant="outline"
            @click="currentStep--"
          >
            Previous Step
          </Button>
          <Button
            v-if="currentStep < 5"
            @click="currentStep++"
          >
            Next Step
          </Button>
        </div>
      </div>
    </div>

    <!-- Schedule Modal -->
    <Dialog v-model:open="showScheduleModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Schedule Campaign</DialogTitle>
          <DialogDescription>
            Choose when to send your campaign
          </DialogDescription>
        </DialogHeader>
        <CampaignScheduler
          :timezone="form.settings.schedule_timezone"
          @schedule="schedule"
        />
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
