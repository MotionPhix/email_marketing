<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import type { CampaignFormProps } from '../../../types'
import { Editor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { useToast } from '@/Components/ui/toast/use-toast'

const props = defineProps<CampaignFormProps>()

const { toast } = useToast()

const editor = new Editor({
  extensions: [
    StarterKit,
  ],
  content: props.campaign?.content || '',
  onUpdate: ({ editor }) => {
    form.content = editor.getHTML()
  },
})

const form = useForm({
  name: props.campaign?.name || '',
  subject: props.campaign?.subject || '',
  content: props.campaign?.content || '',
  from_name: props.campaign?.from_name || props.defaultFromName || '',
  from_email: props.campaign?.from_email || props.defaultFromEmail || '',
  reply_to: props.campaign?.reply_to || '',
  list_ids: props.campaign?.lists.map(list => list.id) || [],
  scheduled_at: props.campaign?.scheduled_at || null,
})

const showSchedule = ref(false)
const scheduleDate = ref<Date | null>(
  props.campaign?.scheduled_at ? new Date(props.campaign.scheduled_at) : null
)

const submit = () => {
  if (showSchedule.value && scheduleDate.value) {
    form.scheduled_at = scheduleDate.value.toISOString()
  }

  const route = props.campaign
    ? `/campaigns/${props.campaign.uuid}`
    : '/campaigns'

  form.post(route, {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: `Campaign ${props.campaign ? 'updated' : 'created'} successfully`,
        variant: 'success',
      })
    },
  })
}

const isValid = computed(() => {
  return form.name &&
    form.subject &&
    form.content &&
    form.list_ids.length > 0 &&
    form.from_name &&
    form.from_email
})
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <Card>
      <CardHeader>
        <CardTitle>Campaign Details</CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
          <FormField>
            <Label>Campaign Name</Label>
            <Input
              v-model="form.name"
              placeholder="Enter campaign name"
              :error="form.errors.name"
            />
          </FormField>

          <FormField>
            <Label>Subject Line</Label>
            <Input
              v-model="form.subject"
              placeholder="Enter email subject"
              :error="form.errors.subject"
            />
          </FormField>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <FormField>
            <Label>From Name</Label>
            <Input
              v-model="form.from_name"
              placeholder="Sender name"
              :error="form.errors.from_name"
            />
          </FormField>

          <FormField>
            <Label>From Email</Label>
            <Input
              v-model="form.from_email"
              type="email"
              placeholder="sender@example.com"
              :error="form.errors.from_email"
            />
          </FormField>

          <FormField>
            <Label>Reply-To (Optional)</Label>
            <Input
              v-model="form.reply_to"
              type="email"
              placeholder="reply@example.com"
              :error="form.errors.reply_to"
            />
          </FormField>
        </div>

        <FormField>
          <Label>Mailing Lists</Label>
          <Select
            v-model="form.list_ids"
            :options="lists"
            multiple
            :error="form.errors.list_ids"
          >
            <template #option="{ option: list }">
              <div class="flex justify-between">
                <span>{{ list.name }}</span>
                <span class="text-muted-foreground">
                  {{ list.subscriber_count }} subscribers
                </span>
              </div>
            </template>
          </Select>
        </FormField>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Email Content</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="prose prose-sm max-w-none">
          <editor-content :editor="editor" />
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle>Schedule</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div class="flex items-center space-x-2">
            <Switch v-model="showSchedule" />
            <Label>Schedule this campaign for later</Label>
          </div>

          <Collapse :show="showSchedule">
            <DateTimePicker
              v-model="scheduleDate"
              :min-date="new Date()"
              :error="form.errors.scheduled_at"
            />
          </Collapse>
        </div>
      </CardContent>
    </Card>

    <div class="flex justify-end space-x-4">
      <Button
        type="button"
        variant="outline"
        :disabled="form.processing"
        @click="$inertia.get('/campaigns')"
      >
        Cancel
      </Button>

      <Button
        type="submit"
        :disabled="form.processing || !isValid"
      >
        {{ props.campaign ? 'Update' : 'Create' }} Campaign
        <Progress
          v-if="form.processing"
          class="ml-2 h-4 w-4"
          :indeterminate="true"
        />
      </Button>
    </div>
  </form>
</template>
