<script setup lang="ts">
import { ref } from 'vue'
import {PlusIcon, ArrowLeftIcon, ArrowRightIcon} from "lucide-vue-next";

const emit = defineEmits(['next', 'back'])
const selectedTemplate = ref<string | null>(null)

const templates = [
  {
    id: 'newsletter',
    name: 'Newsletter',
    description: 'A clean, modern newsletter template',
    preview: '/images/templates/newsletter.png'
  },
  {
    id: 'welcome',
    name: 'Welcome Email',
    description: 'Perfect for welcoming new subscribers',
    preview: '/images/templates/welcome.png'
  },
  {
    id: 'promotion',
    name: 'Promotion',
    description: 'Highlight your products or services',
    preview: '/images/templates/promotion.png'
  },
  {
    id: 'announcement',
    name: 'Announcement',
    description: 'Share important updates',
    preview: '/images/templates/announcement.png'
  }
]

const customizeTemplate = () => {
  // This would normally navigate to the template editor
  // For now, we'll just proceed to the next step
  emit('next')
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Choose a Template</CardTitle>
      <CardDescription>
        Select a template to get started or create your own
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-2">
        <Card
          v-for="template in templates"
          :key="template.id"
          class="cursor-pointer transition-colors hover:bg-muted/50"
          :class="{'ring-2 ring-primary': selectedTemplate === template.id}"
          @click="selectedTemplate = template.id"
        >
          <CardHeader class="p-4">
            <img
              :src="template.preview"
              :alt="template.name"
              class="aspect-video rounded-md object-cover"
            />
          </CardHeader>
          <CardContent class="p-4 pt-0">
            <h4 class="font-medium">{{ template.name }}</h4>
            <p class="text-sm text-muted-foreground">
              {{ template.description }}
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="relative">
        <div class="absolute inset-0 flex items-center">
          <span class="w-full border-t" />
        </div>
        <div class="relative flex justify-center text-xs uppercase">
          <span class="bg-background px-2 text-muted-foreground">
            Or
          </span>
        </div>
      </div>

      <div class="flex justify-center">
        <Button variant="outline">
          <PlusIcon class="mr-2 h-4 w-4" />
          Create from scratch
        </Button>
      </div>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="$emit('back')"
        >
          <ArrowLeftIcon class="mr-2 h-4 w-4" />
          Back
        </Button>
        <Button
          @click="customizeTemplate"
          :disabled="!selectedTemplate"
        >
          Customize Template
          <ArrowRightIcon class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
