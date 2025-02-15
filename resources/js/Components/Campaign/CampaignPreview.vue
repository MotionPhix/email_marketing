<script setup lang="ts">
import { ref } from 'vue'
import { Icon } from '@tabler/icons-vue'

const props = defineProps<{
  campaign: {
    subject: string
    from_name: string
    from_email: string
    content: string
  }
}>()

const previewMode = ref<'desktop' | 'mobile'>('desktop')
const previewProfile = ref('default')

const profiles = {
  default: {
    subscriber: {
      first_name: 'John',
      last_name: 'Doe',
      email: 'john@example.com',
    },
  },
  premium: {
    subscriber: {
      first_name: 'Jane',
      last_name: 'Smith',
      email: 'jane@example.com',
      membership: 'premium',
    },
  },
  new: {
    subscriber: {
      first_name: 'Mike',
      last_name: 'Johnson',
      email: 'mike@example.com',
      signup_date: new Date().toISOString(),
    },
  },
}

const showSource = ref(false)

const processedContent = computed(() => {
  // Replace variables with profile data
  let content = props.campaign.content
  const profile = profiles[previewProfile.value]

  Object.entries(profile).forEach(([category, values]) => {
    Object.entries(values).forEach(([key, value]) => {
      const variable = `{{${category}.${key}}}`
      content = content.replace(new RegExp(variable, 'g'), String(value))
    })
  })

  return content
})
</script>

<template>
  <div class="space-y-6">
    <!-- Preview Controls -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Button
          variant="outline"
          size="icon"
          :class="{ 'bg-primary/10': previewMode === 'desktop' }"
          @click="previewMode = 'desktop'">
          <Icon name="device-desktop" class="h-4 w-4" />
        </Button>

        <Button
          variant="outline"
          size="icon"
          :class="{ 'bg-primary/10': previewMode === 'mobile' }"
          @click="previewMode = 'mobile'">
          <Icon name="device-mobile" class="h-4 w-4" />
        </Button>
      </div>

      <div class="flex items-center space-x-4">
        <Select v-model="previewProfile">
          <option value="default">Default Profile</option>
          <option value="premium">Premium Customer</option>
          <option value="new">New Customer</option>
        </Select>

        <Button
          variant="outline"
          :class="{ 'bg-primary/10': showSource }"
          @click="showSource = !showSource">
          <Icon name="code" class="mr-2 h-4 w-4" />
          View Source
        </Button>
      </div>
    </div>

    <!-- Email Preview -->
    <div
      class="overflow-hidden rounded-lg border bg-white"
      :class="{
        'w-full max-w-[600px] mx-auto': previewMode === 'desktop',
        'w-[375px] mx-auto': previewMode === 'mobile',
      }">
      <!-- Email Header -->
      <div class="border-b p-4">
        <div class="space-y-1">
          <p class="text-sm text-gray-500">From: {{ campaign.from_name }} &lt;{{ campaign.from_email }}&gt;</p>
          <p class="text-sm text-gray-500">Subject: {{ campaign.subject }}</p>
        </div>
      </div>

      <!-- Email Content -->
      <div class="p-4">
        <div v-if="showSource" class="overflow-x-auto">
          <pre class="text-sm"><code>{{ processedContent }}</code></pre>
        </div>
        <div v-else class="prose max-w-none" v-html="processedContent" />
      </div>
    </div>
  </div>
</template>
