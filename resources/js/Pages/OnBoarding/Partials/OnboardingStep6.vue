<script setup lang="ts">
import { ref, computed } from 'vue'
import {CheckCircleIcon, ArrowRightIcon, ArrowLeftIcon, SendIcon, Loader2Icon} from "lucide-vue-next";

const emit = defineEmits(['next', 'back'])

const form = ref({
  subject: 'My First Test Index',
  preview: 'Testing my email marketing setup',
  from_name: '',
  reply_to: '',
  test_email: ''
})

const isSending = ref(false)
const testSent = ref(false)

const isValid = computed(() => {
  return form.value.subject &&
    form.value.from_name &&
    form.value.reply_to &&
    form.value.test_email
})

const sendTestEmail = async () => {
  if (!isValid.value) return

  isSending.value = true

  // Simulate sending test email
  setTimeout(() => {
    isSending.value = false
    testSent.value = true
  }, 2000)
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Send a Test Index</CardTitle>
      <CardDescription>
        Send yourself a test email to verify everything is working
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <!-- Index Details -->
      <div class="space-y-4">
        <FormField>
          <Label>Subject Line</Label>
          <Input
            v-model="form.subject"
            placeholder="Enter email subject"
            :disabled="isSending"
          />
        </FormField>

        <FormField>
          <Label>Preview Text</Label>
          <Input
            v-model="form.preview"
            placeholder="Brief preview text"
            :disabled="isSending"
          />
          <p class="text-xs text-muted-foreground mt-1">
            This text appears in the inbox preview
          </p>
        </FormField>

        <div class="grid gap-4 sm:grid-cols-2">
          <FormField>
            <Label>From Name</Label>
            <Input
              v-model="form.from_name"
              placeholder="Your name or company"
              :disabled="isSending"
            />
          </FormField>

          <FormField>
            <Label>Reply-To Email</Label>
            <Input
              v-model="form.reply_to"
              type="email"
              placeholder="replies@yourdomain.com"
              :disabled="isSending"
            />
          </FormField>
        </div>

        <Separator />

        <FormField>
          <Label>Send Test To</Label>
          <Input
            v-model="form.test_email"
            type="email"
            placeholder="your@email.com"
            :disabled="isSending"
          />
          <p class="text-xs text-muted-foreground mt-1">
            Enter the email address where you want to receive the test
          </p>
        </FormField>
      </div>

      <!-- Preview Card -->
      <Card class="bg-muted">
        <CardHeader>
          <CardTitle class="text-sm">Email Preview</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-2">
            <div class="text-sm">
              <span class="font-medium">From:</span>
              {{ form.from_name || 'Sender Name' }}
              &lt;{{ form.reply_to || 'sender@example.com' }}&gt;
            </div>
            <div class="text-sm">
              <span class="font-medium">Subject:</span>
              {{ form.subject }}
            </div>
            <div class="text-sm">
              <span class="font-medium">Preview:</span>
              {{ form.preview }}
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Success Message -->
      <Alert v-if="testSent" variant="success">
        <CheckCircleIcon class="h-4 w-4" />
        <AlertTitle>Test Email Sent!</AlertTitle>
        <AlertDescription>
          Check your inbox for the test email. It may take a few minutes to arrive.
        </AlertDescription>
      </Alert>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="$emit('back')"
          :disabled="isSending">
          <ArrowLeftIcon class="mr-2 h-4 w-4" />
          Back
        </Button>

        <div class="space-x-2">
          <Button
            variant="secondary"
            @click="sendTestEmail"
            :disabled="!isValid || isSending">
            <SendIcon
              v-if="!isSending"
              class="mr-2 h-4 w-4"
            />

            <Loader2Icon
              v-else
              class="mr-2 h-4 w-4 animate-spin"
            />
            {{ isSending ? 'Sending...' : 'Send Test' }}
          </Button>

          <Button
            @click="$emit('next')"
            :disabled="!testSent">
            Complete Setup
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
