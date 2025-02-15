<script setup lang="ts">
import { ref } from 'vue'
import { Icon } from '@tabler/icons-vue'

const props = defineProps<{
  template: {
    id: number
    name: string
    subject: string
    content: string
    preview_text: string
  }
}>()

const viewMode = ref<'desktop' | 'mobile'>('desktop')
const showSource = ref(false)

const previewFrame = ref<HTMLIFrameElement | null>(null)

const injectStyles = () => {
  if (!previewFrame.value) return

  const styles = `
    body {
      margin: 0;
      padding: 20px;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    img {
      max-width: 100%;
      height: auto;
    }
  `

  const doc = previewFrame.value.contentDocument
  if (!doc) return

  const styleElement = doc.createElement('style')
  styleElement.textContent = styles
  doc.head.appendChild(styleElement)
  doc.body.innerHTML = props.template.content
}
</script>

<template>
  <div class="container mx-auto py-6">
    <Card class="mb-6">
      <CardHeader>
        <CardTitle>{{ template.name }}</CardTitle>
        <CardDescription>Preview how your email will look to recipients</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="flex items-center justify-between">
          <div class="space-y-1">
            <p class="font-medium">Subject: {{ template.subject }}</p>
            <p class="text-sm text-muted-foreground">
              Preview text: {{ template.preview_text }}
            </p>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex items-center space-x-2">
              <Button
                variant="outline"
                size="icon"
                :class="{ 'bg-primary/10': viewMode === 'desktop' }"
                @click="viewMode = 'desktop'"
              >
                <Icon name="device-desktop" class="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                :class="{ 'bg-primary/10': viewMode === 'mobile' }"
                @click="viewMode = 'mobile'"
              >
                <Icon name="device-mobile" class="h-4 w-4" />
              </Button>
            </div>
            <Button
              variant="outline"
              :class="{ 'bg-primary/10': showSource }"
              @click="showSource = !showSource"
            >
              <Icon name="code" class="mr-2 h-4 w-4" />
              View Source
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="relative">
      <div
        :class="[
          'mx-auto rounded-lg border bg-white',
          viewMode === 'mobile' ? 'w-[375px]' : 'w-full max-w-[800px]',
        ]"
      >
        <div v-if="showSource" class="p-4">
          <pre class="rounded-lg bg-muted p-4"><code>{{ template.content }}</code></pre>
        </div>
        <div v-else>
          <iframe
            ref="previewFrame"
            :class="[
              'h-[600px] w-full rounded-lg',
              viewMode === 'mobile' ? 'w-[375px]' : 'w-full',
            ]"
            @load="injectStyles"
          />
        </div>
      </div>
    </div>
  </div>
</template>
