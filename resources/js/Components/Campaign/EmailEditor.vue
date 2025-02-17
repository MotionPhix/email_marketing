<script setup lang="ts">
import { ref, watch } from 'vue'
import {} from '@tinymce/tinymce-vue'
import ConditionalBlockEditor from "@/Components/Campaign/ConditionalBlockEditor.vue";
import {defaultEditorConfig} from "@/config/tinymce";

const props = defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const content = ref(props.modelValue)
const showConditionalEditor = ref(false)

const customEditorConfig = {
  ...defaultEditorConfig,
  setup: (ed: any) => {
    ed.ui.registry.addButton('variables', {
      text: 'Insert Variable',
      icon: 'user',
      onAction: () => {
        showVariables.value = true
      }
    })

    ed.ui.registry.addButton('conditional', {
      text: 'Add Condition',
      icon: 'code-branch',
      onAction: () => {
        showConditionalEditor.value = true
      }
    })
  },
  toolbar: defaultEditorConfig.toolbar + ' | variables conditional',
}

const insertConditional = (condition: string) => {
  if (editor.value) {
    editor.value.editor.insertContent(condition)
    showConditionalEditor.value = false
  }
}

watch(() => props.modelValue, (newValue) => {
  if (newValue !== content.value) {
    content.value = newValue
  }
})

const editorConfig = {
  height: 500,
  menubar: true,
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
  ],
  toolbar: 'undo redo | formatselect | ' +
    'bold italic backcolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | help',
  content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
}
</script>

<template>
  <div class="relative">
    <Editor
      v-model="content"
      :init="editorConfig"
      @update:modelValue="emit('update:modelValue', $event)"
    />

    <Dialog v-model:open="showConditionalEditor">
      <DialogContent class="sm:max-w-[600px]">
        <ConditionalBlockEditor :on-insert="insertConditional" />
      </DialogContent>
    </Dialog>

    <Card>
      <CardHeader>
        <CardTitle class="text-sm">Preview with Sample Data</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="mb-4 flex items-center gap-4">
          <Select v-model="previewProfile">
            <option value="default">Default Profile</option>
            <option value="premium">Premium Customer</option>
            <option value="new">New Customer</option>
          </Select>
          <Button variant="outline" @click="updatePreview">
            Update Preview
          </Button>
        </div>
        <div
          class="prose prose-sm max-h-[300px] overflow-y-auto rounded-lg border p-4 dark:prose-invert"
          v-html="previewContent"
        />
      </CardContent>
    </Card>
  </div>
</template>
