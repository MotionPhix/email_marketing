<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Icon } from '@tabler/icons-vue'
import { useToast } from '@/composables/useToast'

const props = defineProps<{
  template?: {
    id: number
    name: string
    description: string
    subject: string
    content: string
    preview_text: string
    category: string
    variables: string[]
  }
}>()

const { toast } = useToast()

const form = useForm({
  name: props.template?.name ?? '',
  description: props.template?.description ?? '',
  subject: props.template?.subject ?? '',
  content: props.template?.content ?? '',
  preview_text: props.template?.preview_text ?? '',
  category: props.template?.category ?? 'newsletter',
  variables: props.template?.variables ?? [],
})

const isEditing = computed(() => !!props.template)

const submit = () => {
  if (isEditing.value) {
    form.put(route('templates.update', props.template.id), {
      onSuccess: () => {
        toast({
          title: 'Success',
          description: 'Template updated successfully',
        })
      },
    })
  } else {
    form.post(route('templates.store'), {
      onSuccess: () => {
        toast({
          title: 'Success',
          description: 'Template created successfully',
        })
      },
    })
  }
}
</script>

<template>
  <div class="container mx-auto py-6">
    <form @submit.prevent="submit">
      <div class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>{{ isEditing ? 'Edit' : 'Create' }} Template</CardTitle>
            <CardDescription>
              Design your email template with our visual editor
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid gap-6">
              <div class="grid gap-2">
                <Label for="name">Template Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  placeholder="e.g., Welcome Email"
                />
                <span
                  v-if="form.errors.name"
                  class="text-sm text-destructive"
                >
                  {{ form.errors.name }}
                </span>
              </div>

              <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  placeholder="Describe the purpose of this template"
                />
              </div>

              <div class="grid gap-2">
                <Label for="category">Category</Label>
                <Select
                  id="category"
                  v-model="form.category"
                >
                  <option value="newsletter">Newsletter</option>
                  <option value="promotional">Promotional</option>
                  <option value="transactional">Transactional</option>
                </Select>
              </div>

              <div class="grid gap-2">
                <Label for="subject">Email Subject</Label>
                <Input
                  id="subject"
                  v-model="form.subject"
                  placeholder="Subject line"
                />
                <span
                  v-if="form.errors.subject"
                  class="text-sm text-destructive"
                >
                  {{ form.errors.subject }}
                </span>
              </div>

              <div class="grid gap-2">
                <Label for="preview">Preview Text</Label>
                <Input
                  id="preview"
                  v-model="form.preview_text"
                  placeholder="Brief preview text shown in email clients"
                />
              </div>

              <div class="grid gap-2">
                <Label>Template Content</Label>
                <EmailEditor
                  v-model="form.content"
                  :variables="form.variables"
                />
                <span
                  v-if="form.errors.content"
                  class="text-sm text-destructive"
                >
                  {{ form.errors.content }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <div class="flex justify-end space-x-4">
          <Button
            type="button"
            variant="outline"
            :disabled="form.processing"
            @click="$inertia.get(route('templates.index'))"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <Icon
              v-if="form.processing"
              name="loader-2"
              class="mr-2 h-4 w-4 animate-spin"
            />
            {{ isEditing ? 'Update' : 'Create' }} Template
          </Button>
        </div>
      </div>
    </form>
  </div>
</template>
