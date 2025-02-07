<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import type { Campaign } from '../../types'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps<{
  campaign: Campaign
}>()

const { toast } = useToast()
const showTestEmailDialog = ref(false)
const testEmail = ref('')

const sendTestEmail = () => {
  window.axios.post(route('campaigns.send-test', props.campaign.uuid), {
    email: testEmail.value
  })
    .then(() => {
      showTestEmailDialog.value = false
      testEmail.value = ''
      toast({
        title: 'Success',
        description: 'Test email sent successfully',
        variant: 'success'
      })
    })
    .catch(() => {
      toast({
        title: 'Error',
        description: 'Failed to send test email',
        variant: 'destructive'
      })
    })
}
</script>

<template>
  <AppLayout :title="`Preview: ${campaign.name}`">
    <Head :title="`Preview: ${campaign.name}`" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold">Preview Campaign</h2>
            <p class="text-sm text-muted-foreground">
              Preview how your campaign will look to recipients
            </p>
          </div>

          <div class="flex space-x-4">
            <Button
              variant="outline"
              @click="showTestEmailDialog = true"
            >
              Send Test Email
            </Button>
            <Button
              variant="outline"
              @click="$inertia.get(route('campaigns.edit', campaign.uuid))"
            >
              Edit Campaign
            </Button>
          </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <!-- Campaign Details -->
          <Card>
            <CardHeader>
              <CardTitle>Campaign Details</CardTitle>
            </CardHeader>
            <CardContent>
              <dl class="space-y-4">
                <div>
                  <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                  <dd>
                    <Badge :variant="campaign.status">
                      {{ campaign.status }}
                    </Badge>
                  </dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-muted-foreground">From</dt>
                  <dd>{{ campaign.from_name }} &lt;{{ campaign.from_email }}&gt;</dd>
                </div>

                <div v-if="campaign.reply_to">
                  <dt class="text-sm font-medium text-muted-foreground">Reply-To</dt>
                  <dd>{{ campaign.reply_to }}</dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-muted-foreground">Lists</dt>
                  <dd>
                    <ul class="space-y-1">
                      <li v-for="list in campaign.lists" :key="list.id">
                        {{ list.name }} ({{ list.subscriber_count }} subscribers)
                      </li>
                    </ul>
                  </dd>
                </div>

                <div v-if="campaign.scheduled_at">
                  <dt class="text-sm font-medium text-muted-foreground">Scheduled For</dt>
                  <dd>{{ new Date(campaign.scheduled_at).toLocaleString() }}</dd>
                </div>
              </dl>
            </CardContent>
          </Card>

          <!-- Email Preview -->
          <Card class="md:col-span-2">
            <CardHeader class="border-b">
              <CardTitle>{{ campaign.subject }}</CardTitle>
            </CardHeader>
            <CardContent class="prose prose-sm max-w-none p-6">
              <div v-html="campaign.content" />
            </CardContent>
          </Card>
        </div>

        <!-- Test Email Dialog -->
        <Dialog v-model:open="showTestEmailDialog">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Send Test Email</DialogTitle>
              <DialogDescription>
                Send a test email to verify how your campaign will look
              </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
              <FormField>
                <Label>Email Address</Label>
                <Input
                  v-model="testEmail"
                  type="email"
                  placeholder="Enter your email address"
                />
              </FormField>
            </div>

            <DialogFooter>
              <Button
                variant="outline"
                @click="showTestEmailDialog = false"
              >
                Cancel
              </Button>
              <Button
                :disabled="!testEmail"
                @click="sendTestEmail"
              >
                Send Test
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AppLayout>
</template>
