<script setup lang="ts">
import {computed, ref} from 'vue'
import {ArrowLeftIcon, ArrowRightIcon, UploadCloudIcon, FileIcon, PlusIcon, TrashIcon } from "lucide-vue-next"
import {useStorage} from "@vueuse/core";
import {router} from "@inertiajs/vue3";
import {toast} from "vue-sonner";

const emit = defineEmits(['next', 'back'])
const importMethod = useStorage<'upload' | 'paste' | 'manual'>('import_method', 'upload')
const file = ref<File | null>(null)
const isUploading = ref(false)
const uploadProgress = ref(0)
const isLoading = ref(false)
const errors = ref({})

// For paste entry
const pastedData = ref('')

const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (input.files?.length) {
    const selectedFile = input.files[0]
    if (selectedFile.type !== 'text/csv' && !selectedFile.name.endsWith('.csv')) {
      toast.error('Please upload a CSV file')
      return
    }
    file.value = selectedFile
  }
}


const manualContacts = ref([{ email: '', first_name: '', last_name: '' }])

const addManualContact = () => {
  manualContacts.value.push({ email: '', first_name: '', last_name: '' })
}

const removeManualContact = (index: number) => {
  if (manualContacts.value.length > 1) {
    manualContacts.value.splice(index, 1)
  }
}

const parsePastedData = (data: string) => {
  return data.split('\n')
    .map(line => {
      const [email, firstName, lastName] = line.split(',').map(item => item.trim())
      if (email) {
        return {
          email,
          first_name: firstName || '',
          last_name: lastName || ''
        }
      }
      return null
    })
    .filter(contact => contact !== null)
}

const processCsvFile = async (file: File) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = (event) => {
      try {
        const csv = event.target?.result as string
        const contacts = parsePastedData(csv)
        resolve(contacts)
      } catch (error) {
        reject(error)
      }
    }
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.readAsText(file)
  })
}

const isValid = computed(() => {
  if (importMethod.value === 'upload') {
    return !!file.value
  } else if (importMethod.value === 'paste') {
    return !!pastedData.value.trim()
  } else {
    return manualContacts.value.some(contact => contact.email.trim())
  }
})

const handleSubmit = async () => {
  try {
    isLoading.value = true
    let contacts = <any>[]

    if (importMethod.value === 'upload' && file.value) {
      contacts = await processCsvFile(file.value)
    } else if (importMethod.value === 'paste') {
      contacts = parsePastedData(pastedData.value)
    } else if (importMethod.value === 'manual') {
      contacts = manualContacts.value.filter(contact => contact.email.trim())
    }

    if (!contacts.length) {
      toast.error('Please add at least one contact')
      return
    }

    console.log(contacts)

    router.post(route('onboarding.update-step'), {
      step: 3,
      data: {
        contacts
      }
    }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Contacts imported successfully')
        emit('next')
      },
      onError: (validationErrors) => {
        errors.value = validationErrors
        const firstError = Object.values(validationErrors)[0]
        toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
      },
      onFinish: () => {
        isLoading.value = false
      }
    })

  } catch (error) {
    toast.error('Failed to process contacts')
    isLoading.value = false
  }
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
          <div
            class="grid w-full place-items-center gap-4 rounded-lg border border-dashed p-6"
            :class="{ 'border-destructive': errors['data.contacts'] }">
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
            v-model="uploadProgress"
            class="h-2"
          />

          <small class="text-destructive" v-if="errors['data.contacts']">
            {{ errors['data.contacts'] }}
          </small>
        </TabsContent>

        <TabsContent value="paste" class="space-y-4">
          <FormField
            placeholder="Paste your subscriber data here..."
            type="textarea"
            rows="10"
          />

          <p class="text-xs text-muted-foreground">
            Format: Email, First Name, Last Name (one per line)
          </p>

          <small class="text-destructive" v-if="errors['data.contacts']">
            {{ errors['data.contacts'] }}
          </small>
        </TabsContent>

        <TabsContent value="manual" class="space-y-4">
          <div v-for="(contact, index) in manualContacts" :key="index" class="space-y-4">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-medium">Contact {{ index + 1 }}</h4>
              <Button
                v-if="manualContacts.length > 1"
                variant="ghost"
                size="icon"
                @click="removeManualContact(index)">
                <TrashIcon class="h-4 w-4" />
              </Button>
            </div>

            <div class="grid gap-4">
              <div>
                <FormField
                  label="Email"
                  type="email"
                  v-model="contact.email"
                  placeholder="subscriber@example.com"
                  :error="errors[`data.contacts.${index}.email`]"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <FormField
                    label="First Name"
                    type="text"
                    v-model="contact.first_name"
                    placeholder="John"
                    :error="errors[`data.contacts.${index}.first_name`]"
                  />
                </div>

                <div>
                  <FormField
                    label="Last Name"
                    type="text"
                    v-model="contact.last_name"
                    placeholder="Doe"
                    :error="errors[`data.contacts.${index}.last_name`]"
                  />
                </div>
              </div>
            </div>
          </div>

          <Button
            variant="outline"
            class="w-full"
            @click="addManualContact">
            <PlusIcon class="mr-2 h-4 w-4" />
            Add Another Contact
          </Button>
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
          @click="handleSubmit"
          :disabled="!isValid || isLoading">
          {{ isLoading ? 'Processing...' : 'Continue' }}
          <ArrowRightIcon v-if="!isLoading" class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
