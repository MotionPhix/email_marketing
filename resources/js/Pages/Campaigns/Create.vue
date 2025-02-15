<script setup lang="ts">
import { ref, computed } from 'vue'
import { Icon } from '@tabler/icons-vue'
import { useForm } from '@inertiajs/vue3'
import type { CampaignDraft } from '@/types/campaign'
import EmailEditor from '@/Components/EmailEditor.vue'
import RecipientSelector from '@/Components/RecipientSelector.vue'
import ScheduleSelector from '@/Components/ScheduleSelector.vue'

const currentStep = ref(1)
const steps = [
  { id: 1, name: 'Details', icon: 'file-description' },
  { id: 2, name: 'Content', icon: 'mail-forward' },
  { id: 3, name: 'Recipients', icon: 'users' },
  { id: 4, name: 'Schedule', icon: 'calendar' },
  { id: 5, name: 'Review', icon: 'checklist' },
]

const form = useForm<CampaignDraft>({
  name: '',
  subject: '',
  fromName: '',
  fromEmail: '',
  replyTo: '',
  content: '',
  previewText: '',
  scheduledAt: undefined,
  recipients: {
    segments: [],
    lists: [],
    excludedLists: [],
  },
  settings: {
    trackOpens: true,
    trackClicks: true,
    autoInlineCss: true,
  },
})

const canGoNext = computed(() => {
  switch (currentStep.value) {
    case 1:
      return form.name && form.subject && form.fromName && form.fromEmail
    case 2:
      return form.content.length > 0
    case 3:
      return form.recipients.lists?.length > 0 || form.recipients.segments?.length > 0
    case 4:
      return true // Schedule is optional
    default:
      return false
  }
})

const goToStep = (step: number) => {
  if (step > currentStep.value && !canGoNext.value) return
  currentStep.value = step
}

const submit = () => {
  form.post(route('campaigns.store'), {
    onSuccess: () => {
      // Show success toast
      // Redirect to campaign list or preview
    },
  })
}
</script>

<template>
  <div class="container mx-auto py-6">
    <!-- Steps -->
    <nav aria-label="Progress">
      <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0">
        <li v-for="step in steps" :key="step.id" class="md:flex-1">
          <button
            :class="[
              'group flex w-full flex-col border-l-4 py-2 pl-4 hover:border-slate-400 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4',
              currentStep === step.id
                ? 'border-primary text-primary'
                : currentStep > step.id
                ? 'border-success text-success'
                : 'border-slate-200 hover:border-slate-300',
            ]"
            @click="goToStep(step.id)"
          >
            <span class="text-sm font-medium">{{ step.name }}</span>
            <span class="text-sm">Step {{ step.id }} of {{ steps.length }}</span>
          </button>
        </li>
      </ol>
    </nav>

    <!-- Form Steps -->
    <div class="mt-8">
      <!-- Step 1: Details -->
      <div v-show="currentStep === 1" class="space-y-4">
        <Card>
          <CardHeader>
            <CardTitle>Campaign Details</CardTitle>
            <CardDescription>
              Set up the basic information for your email campaign
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid gap-4">
              <div class="grid gap-2">
                <Label for="name">Campaign Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  placeholder="e.g., February Newsletter"
                />
                <span v-if="form.errors.name" class="text-sm text-destructive">
                  {{ form.errors.name }}
                </span>
              </div>

              <div class="grid gap-2">
                <Label for="subject">Email Subject</Label>
                <Input
                  id="subject"
                  v-model="form.subject"
                  placeholder="Your email subject line"
                />
                <span v-if="form.errors.subject" class="text-sm text-destructive">
                  {{ form.errors.subject }}
                </span>
              </div>

              <div class="grid gap-2">
                <Label for="previewText">Preview Text (optional)</Label>
                <Input
                  id="previewText"
                  v-model="form.previewText"
                  placeholder="Brief preview text shown in email clients"
                />
              </div>

              <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                  <Label for="fromName">From Name</Label>
                  <Input
                    id="fromName"
                    v-model="form.fromName"
                    placeholder="Sender's name"
                  />
                  <span v-if="form.errors.fromName" class="text-sm text-destructive">
                    {{ form.errors.fromName }}
                  </span>
                </div>

                <div class="grid gap-2">
                  <Label for="fromEmail">From Email</Label>
                  <Input
                    id="fromEmail"
                    v-model="form.fromEmail"
                    type="email"
                    placeholder="sender@example.com"
                  />
                  <span v-if="form.errors.fromEmail" class="text-sm text-destructive">
                    {{ form.errors.fromEmail }}
                  </span>
                </div>
              </div>

              <div class="grid gap-2">
                <Label for="replyTo">Reply-To Email (optional)</Label>
                <Input
                  id="replyTo"
                  v-model="form.replyTo"
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
              Design your email using our drag-and-drop editor or HTML
            </CardDescription>
          </CardHeader>
          <CardContent>
            <EmailEditor v-model="form.content" />
            <span v-if="form.errors.content" class="mt-2 text-sm text-destructive">
              {{ form.errors.content }}
            </span>
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
            <RecipientSelector v-model="form.recipients" />
          </CardContent>
        </Card>
      </div>

      <!-- Step 4: Schedule -->
      <div v-show="currentStep === 4">
        <Card>
          <CardHeader>
            <CardTitle>Schedule Campaign</CardTitle>
            <CardDescription>
              Choose when to send your campaign
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ScheduleSelector v-model="form.scheduledAt" />
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
              <div class="rounded-lg border p-4">
                <h4 class="font-medium">Campaign Details</h4>
                <dl class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <dt class="text-sm text-muted-foreground">Name</dt>
                    <dd class="text-sm font-medium">{{ form.name }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-muted-foreground">Subject</dt>
                    <dd class="text-sm font-medium">{{ form.subject }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm text-muted-foreground">From</dt>
                    <dd class="text-sm font-medium">
                      {{ form.fromName }} ({{ form.fromEmail }})
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm text-muted-foreground">Schedule</dt>
                    <dd class="text-sm font-medium">
                      {{ form.scheduledAt || 'Send immediately' }}
                    </dd>
                  </div>
                </dl>
              </div>

              <div class="rounded-lg border p-4">
                <h4 class="font-medium">Recipients</h4>
                <div class="mt-2">
                  <div v-if="form.recipients.lists?.length" class="mb-2">
                    <span class="text-sm text-muted-foreground">Lists: </span>
                    <Badge
                      v-for="list in form.recipients.lists"
                      :key="list"
                      variant="secondary"
                      class="ml-2"
                    >
                      {{ list }}
                    </Badge>
                  </div>
                  <div v-if="form.recipients.segments?.length">
                    <span class="text-sm text-muted-foreground">Segments: </span>
                    <Badge
                      v-for="segment in form.recipients.segments"
                      :key="segment"
                      variant="secondary"
                      class="ml-2"
                    >
                      {{ segment }}
                    </Badge>
                  </div>
                </div>
              </div>

              <div class="rounded-lg border p-4">
                <h4 class="font-medium">Preview</h4>
                <div class="mt-4 max-h-96 overflow-auto rounded border">
                  <div v-html="form.content" class="p-4" />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Navigation -->
      <div class="mt-6 flex justify-between">
        <Button
          v-if="currentStep > 1"
          variant="outline"
          @click="currentStep--"
        >
          <Icon name="arrow-left" class="mr-2 h-4 w-4" />
          Previous
        </Button>
        <div class="flex justify-end space-x-4">
          <Button
            v-if="currentStep < steps.length"
            :disabled="!canGoNext"
            @click="currentStep++"
          >
            Next
            <Icon name="arrow-right" class="ml-2 h-4 w-4" />
          </Button>
          <Button
            v-if="currentStep === steps.length"
            :disabled="form.processing"
            @click="submit"
          >
            <Icon
              v-if="form.processing"
              name="loader-2"
              class="mr-2 h-4 w-4 animate-spin"
            />
            {{ form.scheduledAt ? 'Schedule Campaign' : 'Send Campaign' }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
