<script setup lang="ts">
import { ref } from 'vue'
import {Head, router} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {toast} from "vue-sonner";
import {DownloadIcon, PlusIcon, UploadIcon, PencilIcon, MoreHorizontalIcon, TrashIcon} from "lucide-vue-next";

const props = defineProps<{
  subscribers: {
    data: Array<{
      id: number
      email: string
      first_name: string | null
      last_name: string | null
      company: string | null
      status: string
      created_at: string
    }>
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
  }
}>()

const showImportModal = ref(false)
const importFile = ref<File | null>(null)
const fileError = ref('')

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files?.length) {
    importFile.value = target.files[0]
    fileError.value = ''
  }
}

const submitImport = () => {
  if (!importFile.value) {
    toast.error("Error", {
      description: 'Please select a file to import'
    })

    return
  }

  const formData = new FormData()
  formData.append('file', importFile.value)
  // Submit form logic here
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'subscribed':
      return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300'
    case 'unsubscribed':
      return 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300'
    case 'bounced':
      return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300'
    default:
      return 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300'
  }
}
</script>

<template>
  <AppLayout title="Subscribers">
    <Head title="Subscribers" />

    <div class="container space-y-6 p-4 md:p-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold tracking-tight">Subscribers</h2>
          <p class="text-muted-foreground">Manage your email subscribers and lists</p>
        </div>

        <div class="flex items-center gap-4">
          <Button variant="outline" @click="router.get(route('subscribers.export'))">
            <span class="sr-only">Export subscribers</span>
            <DownloadIcon class="h-4 w-4 mr-2" />
            Export
          </Button>

          <Button variant="outline" @click="showImportModal = true">
            <span class="sr-only">Import subscribers</span>
            <UploadIcon class="h-4 w-4 mr-2" />
            Import
          </Button>

          <Button @click="router.get(route('subscribers.create'))">
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Subscriber
          </Button>
        </div>
      </div>

      <Card>
        <CardContent class="p-0">
          <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs uppercase border-b dark:border-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3">Email</th>
                <th scope="col" class="px-6 py-3">Name</th>
                <th scope="col" class="px-6 py-3">Company</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Date Added</th>
                <th scope="col" class="px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="subscriber in subscribers.data"
                  :key="subscriber.id"
                  class="border-b dark:border-gray-700 hover:bg-muted/50">
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ subscriber.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ [subscriber.first_name, subscriber.last_name].filter(Boolean).join(' ') || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ subscriber.company || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Badge :class="getStatusColor(subscriber.status)">
                    {{ subscriber.status }}
                  </Badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ new Date(subscriber.created_at).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <DropdownMenu>
                    <DropdownMenuTrigger as="button" class="icon-button">
                      <MoreHorizontalIcon class="h-4 w-4" />
                      <span class="sr-only">Open menu</span>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem @click="router.get(route('subscribers.edit', subscriber.id))">
                        <PencilIcon class="h-4 w-4 mr-2" />
                        Edit
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="router.delete(route('subscribers.destroy', subscriber.id))">
                        <TrashIcon class="h-4 w-4 mr-2" />
                        Delete
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Import Modal -->
    <Dialog :open="showImportModal" @update:open="showImportModal = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Import Subscribers</DialogTitle>
          <DialogDescription>
            Upload a CSV file containing your subscribers' information.
          </DialogDescription>
        </DialogHeader>

        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="file">CSV File</Label>
            <Input
              id="file"
              type="file"
              accept=".csv"
              @change="handleFileChange"
            />
            <p class="text-sm text-muted-foreground">
              File must be in CSV format with headers: email, first_name, last_name, company
            </p>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showImportModal = false">
            Cancel
          </Button>
          <Button @click="submitImport">Import</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Toast Provider at the App Root -->
    <ToastProvider />
  </AppLayout>
</template>

<style scoped>
.icon-button {
  @apply inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 w-10;
}
</style>
