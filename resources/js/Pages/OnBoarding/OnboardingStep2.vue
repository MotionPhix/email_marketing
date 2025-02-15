<script setup lang="ts">
import { ref } from 'vue'
import {ArrowLeftIcon, ArrowRightIcon, UploadCloudIcon, FileIcon} from "lucide-vue-next"

const emit = defineEmits(['next', 'back'])
const importMethod = ref<'upload' | 'paste' | 'manual'>('upload')
const file = ref<File | null>(null)
const isUploading = ref(false)
const uploadProgress = ref(0)

const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files?.length) {
    file.value = input.files[0]
  }
}

const handleUpload = async () => {
  if (!file.value) return

  isUploading.value = true
  // Simulated upload progress
  const interval = setInterval(() => {
    if (uploadProgress.value < 100) {
      uploadProgress.value += 10
    } else {
      clearInterval(interval)
      isUploading.value = false
      emit('next')
    }
  }, 500)
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Import Your Contacts</CardTitle>
      <CardDescription>
        Choose how you'd like to add your subscribers
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <Tabs v-model="importMethod" class="w-full">
        <TabsList class="grid w-full grid-cols-3">
          <TabsTrigger value="upload">CSV Upload</TabsTrigger>
          <TabsTrigger value="paste">Paste Data</TabsTrigger>
          <TabsTrigger value="manual">Manual Entry</TabsTrigger>
        </TabsList>
        <TabsContent value="upload" class="space-y-4">
          <div class="grid w-full place-items-center gap-4 rounded-lg border border-dashed p-6">
            <UploadCloudIcon class="h-10 w-10 text-muted-foreground" />
            <div class="text-center">
              <p class="text-sm font-medium">
                Drop your CSV file here or click to upload
              </p>
              <p class="text-xs text-muted-foreground">
                Supported formats: CSV, Excel
              </p>
            </div>
            <Input
              type="file"
              accept=".csv,.xlsx"
              class="max-w-xs"
              @change="handleFileChange"
            />
          </div>

          <Alert v-if="file">
            <FileIcon class="h-4 w-4" />
            <AlertTitle>Selected file</AlertTitle>
            <AlertDescription>
              {{ file.name }} ({{ (file.size / 1024).toFixed(2) }}KB)
            </AlertDescription>
          </Alert>

          <Progress
            v-if="isUploading"
            :value="uploadProgress"
            class="h-2"
          />
        </TabsContent>

        <TabsContent value="paste" class="space-y-4">
          <Textarea
            placeholder="Paste your subscriber data here..."
            rows="10"
          />
          <p class="text-xs text-muted-foreground">
            Format: Email, First Name, Last Name (one per line)
          </p>
        </TabsContent>

        <TabsContent value="manual" class="space-y-4">
          <div class="space-y-2">
            <FormField>
              <Label>Email</Label>
              <Input type="email" placeholder="subscriber@example.com" />
            </FormField>

            <FormField>
              <Label>First Name</Label>
              <Input type="text" placeholder="John" />
            </FormField>

            <FormField>
              <Label>Last Name</Label>
              <Input type="text" placeholder="Doe" />
            </FormField>
          </div>
        </TabsContent>
      </Tabs>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="$emit('back')" >
          <ArrowLeftIcon class="mr-2 h-4 w-4" />
          Back
        </Button>

        <Button
          @click="importMethod === 'upload' ? handleUpload() : $emit('next')"
          :disabled="importMethod === 'upload' && !file" >
          Continue
          <ArrowRightIcon class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
