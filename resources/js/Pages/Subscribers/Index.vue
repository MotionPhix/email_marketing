<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import pickBy from 'lodash/pickBy'
import throttle from 'lodash/throttle'
import AppLayout from '@/Layouts/AppLayout.vue'
import ImportErrorsModal from '@/Pages/Subscribers/Components/ImportErrorsModal.vue'
import EmptyState from "@/Pages/Subscribers/Components/EmptyState.vue";
import {
  Users,
  UserPlus,
  Download,
  Upload,
  MoreHorizontal,
  FileTextIcon,
  Check,
  AlertTriangle,
  XCircle,
  AlertCircle,
  ChevronUp,
  ChevronDown,
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'

const props = defineProps<{
  subscribers: {
    data: Array<{
      id: number
      email: string
      first_name: string
      last_name: string
      company: string | null
      status: string
      metadata: Record<string, any> | null
      unsubscribed_at: string | null
      created_at: string
      campaign_stats: {
        total_received: number
        total_opened: number
        total_clicked: number
      }
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
    total: number
  }
  filters: {
    search: string
    status: string
    sort: string
    direction: 'asc' | 'desc'
  }
  stats: {
    total: number
    subscribed: number
    unsubscribed: number
    bounced: number
    complained: number
  }
}>()

const search = ref(props.filters.search)
const selectedStatus = ref(props.filters.status || 'all')
const sort = ref(props.filters.sort || 'created_at')
const direction = ref(props.filters.direction || 'desc')
const selectedSubscribers = ref<number[]>([])
const isImportDialogOpen = ref(false)
const isAddDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingSubscriber = ref<typeof props.subscribers.data[0] | null>(null)
const importFile = ref<File | null>(null)

const statuses = [
  { value: 'all', label: 'All' },
  { value: 'subscribed', label: 'Subscribed' },
  { value: 'unsubscribed', label: 'Unsubscribed' },
  { value: 'bounced', label: 'Bounced' },
  { value: 'complained', label: 'Complained' },
]

const statusColors = {
  subscribed: 'success',
  unsubscribed: 'default',
  bounced: 'warning',
  complained: 'destructive'
} as const

const form = ref({
  email: '',
  first_name: '',
  last_name: '',
  company: '',
  status: 'subscribed'
})

watch(search, throttle((value) => {
  router.get(
    route('subscribers.index'),
    pickBy({ search: value, status: selectedStatus.value }),
    { preserveState: true, preserveScroll: true }
  )
}, 300))

watch(selectedStatus, (value) => {
  router.get(
    route('subscribers.index'),
    pickBy({ search: search.value, status: value }),
    { preserveState: true, preserveScroll: true }
  )
})

const sortBy = (field: string) => {
  direction.value = sort.value === field && direction.value === 'asc' ? 'desc' : 'asc'
  sort.value = field

  router.get(
    route('subscribers.index'),
    pickBy({
      search: search.value,
      status: selectedStatus.value,
      sort: sort.value,
      direction: direction.value
    }),
    { preserveState: true, preserveScroll: true }
  )
}

const addSubscriber = () => {
  router.post(route('subscribers.store'), form.value, {
    onSuccess: () => {
      isAddDialogOpen.value = false
      form.value = {
        email: '',
        first_name: '',
        last_name: '',
        company: '',
        status: 'subscribed'
      }
    }
  })
}

const updateSubscriber = () => {
  if (!editingSubscriber.value) return

  router.put(route('subscribers.update', editingSubscriber.value.id), editingSubscriber.value, {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editingSubscriber.value = null
    }
  })
}

const deleteSubscriber = (id: number) => {
  if (confirm('Are you sure you want to delete this subscriber?')) {
    router.delete(route('subscribers.destroy', id))
  }
}

const bulkDelete = () => {
  if (confirm('Are you sure you want to delete the selected subscribers?')) {
    router.post(route('subscribers.bulk-destroy'), {
      ids: selectedSubscribers.value
    }, {
      onSuccess: () => {
        selectedSubscribers.value = []
      }
    })
  }
}

const bulkUpdateStatus = (status: string) => {
  router.post(route('subscribers.bulk-update'), {
    ids: selectedSubscribers.value,
    status
  }, {
    onSuccess: () => {
      selectedSubscribers.value = []
    }
  })
}

const handleImport = () => {
  if (!importFile.value) return

  const formData = new FormData()
  formData.append('file', importFile.value)

  router.post(route('subscribers.import'), formData, {
    onSuccess: () => {
      isImportDialogOpen.value = false
      importFile.value = null
    }
  })
}

const downloadExport = () => {
  window.location.href = route('subscribers.export')
}

/*const downloadTemplate = () => {
  const headers = ['Email', 'First Name', 'Last Name', 'Company', 'Status']
  const sample = ['john@example.com', 'John', 'Doe', 'ACME Inc', 'subscribed']

  const csv = [
    headers.join(','),
    sample.join(',')
  ].join('\n')

  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'subscribers-template.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}*/

const downloadTemplate = () => {
  // Headers with descriptions
  const headers = [
    'email,first_name,last_name,company,status',
    '# Required,Required,Required,Optional,Optional (default: subscribed)',
    '# Valid email,String,String,String,One of: subscribed|unsubscribed|bounced|complained',
    ''
  ]

  // Sample data rows
  const sampleData = [
    'john.doe@example.com,John,Doe,ACME Inc,subscribed',
    'jane.smith@example.com,Jane,Smith,Tech Corp,subscribed',
    'mike.jones@example.com,Mike,Jones,,unsubscribed',
    'sarah.wilson@example.com,Sarah,Wilson,StartUp Ltd,bounced'
  ]

  const csv = [
    ...headers,
    ...sampleData
  ].join('\n')

  // Create file metadata object with BOM for Excel compatibility
  const blob = new Blob(['\ufeff' + csv], {
    type: 'text/csv;charset=utf-8'
  })

  // Create download link
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  const timestamp = new Date().toISOString().split('T')[0]

  link.href = url
  link.setAttribute('download', `subscribers-template-${timestamp}.csv`)
  document.body.appendChild(link)
  link.click()

  // Cleanup
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}
</script>

<template>
  <AppLayout title="Subscribers">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Subscribers
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5 mb-6">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Total Subscribers
              </CardTitle>
              <Users class="h-4 w-4 text-muted-foreground" />
            </CardHeader>

            <CardContent>
              <div class="text-2xl font-bold">{{ stats.total }}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Active
              </CardTitle>
              <Check class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.subscribed }}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Unsubscribed
              </CardTitle>
              <XCircle class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.unsubscribed }}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Bounced
              </CardTitle>
              <AlertTriangle class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.bounced }}</div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">
                Complained
              </CardTitle>
              <AlertCircle class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ stats.complained }}</div>
            </CardContent>
          </Card>
        </div>

        <!-- Actions and Filters -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-4">
            <Input
              v-model="search"
              placeholder="Search subscribers..."
              class="w-[300px]"
            />

            <Select
              v-model="selectedStatus">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Filter by status" />
              </SelectTrigger>

              <SelectContent>
                <SelectItem
                  v-for="status in statuses"
                  :key="status.value"
                  :value="status.value">
                  {{ status.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Actions Dropdown -->
          <div class="flex items-center gap-2">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline">
                  Actions
                  <ChevronDown class="ml-2 h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>

              <DropdownMenuContent align="end" class="w-[200px]">
                <DropdownMenuItem @click="isAddDialogOpen = true">
                  <UserPlus class="mr-2 h-4 w-4" />
                  Add Subscriber
                </DropdownMenuItem>

                <DropdownMenuItem @click="isImportDialogOpen = true">
                  <Upload class="mr-2 h-4 w-4" />
                  Import Subscribers
                </DropdownMenuItem>

                <DropdownMenuItem @click="downloadExport">
                  <Download class="mr-2 h-4 w-4" />
                  Export Subscribers
                </DropdownMenuItem>

                <DropdownMenuSeparator />

                <DropdownMenuItem @click="downloadTemplate">
                  <FileTextIcon class="mr-2 h-4 w-4" />
                  Download Template
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedSubscribers.length > 0" class="mb-4 flex items-center gap-2">
          <Button variant="destructive" @click="bulkDelete">
            Delete Selected
          </Button>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline">
                Update Status
                <ChevronDown class="ml-2 h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              <DropdownMenuItem
                v-for="status in statuses.filter(s => s.value !== 'all')"
                :key="status.value"
                @click="bulkUpdateStatus(status.value)"
              >
                Set as {{ status.label }}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          <span class="text-sm text-muted-foreground">
            {{ selectedSubscribers.length }} selected
          </span>
        </div>

        <div v-if="subscribers.data.length === 0 && !search" class="mt-6">
          <EmptyState
            @add="isAddDialogOpen = true"
            @import="isImportDialogOpen = true"
            @template="downloadTemplate"
          />
        </div>

        <div v-else-if="subscribers.data.length === 0 && search" class="mt-6">
          <div class="flex min-h-[400px] flex-col items-center justify-center rounded-md border border-dashed p-8 text-center animate-in fade-in-50">
            <div class="mx-auto flex max-w-[420px] flex-col items-center justify-center text-center">
              <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                <Users class="h-10 w-10 text-muted-foreground" />
              </div>

              <h3 class="mt-4 text-lg font-semibold">No results found</h3>
              <p class="mt-2 text-sm text-muted-foreground">
                No subscribers found matching your search: "{{ search }}"
              </p>

              <div class="mt-6">
                <Button variant="outline" @click="search = ''">
                  Clear search
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Subscribers Table -->
        <div
          v-else
          class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[40px]">
                  <Checkbox
                    :checked="Boolean(selectedSubscribers.length && selectedSubscribers.length === subscribers.data.length)"
                    :indeterminate="selectedSubscribers.length && selectedSubscribers.length > 0 && selectedSubscribers.length < subscribers.data.length"
                    @update:checked="(checked: boolean) => {
                      selectedSubscribers = checked ? subscribers.data.map(s => s.id) : []
                    }"
                  />
                </TableHead>

                <TableHead>
                  <div class="flex items-center gap-1">
                    <button
                      class="inline-flex items-center"
                      @click="sortBy('email')">
                      Email
                      <component
                        :is="sort === 'email' ? (direction === 'asc' ? ChevronUp : ChevronDown) : null"
                        class="ml-1 h-4 w-4"
                      />
                    </button>
                  </div>
                </TableHead>

                <TableHead>
                  <div class="flex items-center gap-1">
                    <button
                      class="inline-flex items-center"
                      @click="sortBy('first_name')">
                      Name
                      <component
                        :is="sort === 'first_name' ? (direction === 'asc' ? ChevronUp : ChevronDown) : null"
                        class="ml-1 h-4 w-4"
                      />
                    </button>
                  </div>
                </TableHead>

                <TableHead>Company</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>
                  <div class="flex items-center gap-1">
                    <button
                      class="inline-flex items-center"
                      @click="sortBy('created_at')">
                      Joined
                      <component
                        :is="sort === 'created_at' ? (direction === 'asc' ? ChevronUp : ChevronDown) : null"
                        class="ml-1 h-4 w-4"
                      />
                    </button>
                  </div>
                </TableHead>
                <TableHead class="w-[70px]"></TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow
                v-for="subscriber in subscribers.data"
                :key="subscriber.id">
                <TableCell>
                  <Checkbox
                    :checked="selectedSubscribers.includes(subscriber.id)"
                    @update:checked="(checked: boolean) => {
                      selectedSubscribers = checked
                        ? [...selectedSubscribers, subscriber.id]
                        : selectedSubscribers.filter(id => id !== subscriber.id)
                    }"
                  />
                </TableCell>

                <TableCell>{{ subscriber.email }}</TableCell>

                <TableCell>{{ subscriber.first_name }} {{ subscriber.last_name }}</TableCell>

                <TableCell>{{ subscriber.company || '-' }}</TableCell>

                <TableCell>
                  <Badge
                    :variant="statusColors[subscriber.status]"
                    class="capitalize">
                    {{ subscriber.status }}
                  </Badge>
                </TableCell>

                <TableCell>
                  {{ new Date(subscriber.created_at).toLocaleDateString() }}
                </TableCell>

                <TableCell>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="ghost" size="icon">
                        <MoreHorizontal class="h-4 w-4" />
                        <span class="sr-only">Open menu</span>
                      </Button>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent align="end">
                      <DropdownMenuItem
                        @click="router.visit(route('subscribers.show', subscriber.id))">
                        Show
                      </DropdownMenuItem>

                      <DropdownMenuItem
                        @click="editingSubscriber = subscriber; isEditDialogOpen = true">
                        Edit
                      </DropdownMenuItem>


                      <DropdownMenuItem @click="deleteSubscriber(subscriber.id)">
                        Delete
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Add Subscriber Dialog -->
        <Dialog v-model:open="isAddDialogOpen">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add Subscriber</DialogTitle>
              <DialogDescription>
                Add a new subscriber to your mailing list.
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>First Name</Label>
                  <Input v-model="form.first_name" />
                </div>
                <div class="space-y-2">
                  <Label>Last Name</Label>
                  <Input v-model="form.last_name" />
                </div>
              </div>
              <div class="space-y-2">
                <Label>Email</Label>
                <Input v-model="form.email" type="email" />
              </div>
              <div class="space-y-2">
                <Label>Company</Label>
                <Input v-model="form.company" />
              </div>
              <div class="space-y-2">
                <Label>Status</Label>
                <Select v-model="form.status">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="status in statuses.filter(s => s.value !== 'all')"
                      :key="status.value"
                      :value="status.value"
                    >
                      {{ status.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <DialogFooter>
              <Button variant="ghost" @click="isAddDialogOpen = false">
                Cancel
              </Button>
              <Button @click="addSubscriber">
                Add Subscriber
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        <!-- Edit Subscriber Dialog -->
        <Dialog v-model:open="isEditDialogOpen">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Subscriber</DialogTitle>
              <DialogDescription>
                Update subscriber information.
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>First Name</Label>
                  <Input v-model="editingSubscriber.first_name" />
                </div>
                <div class="space-y-2">
                  <Label>Last Name</Label>
                  <Input v-model="editingSubscriber.last_name" />
                </div>
              </div>
              <div class="space-y-2">
                <Label>Email</Label>
                <Input v-model="editingSubscriber.email" type="email" />
              </div>
              <div class="space-y-2">
                <Label>Company</Label>
                <Input v-model="editingSubscriber.company" />
              </div>
              <div class="space-y-2">
                <Label>Status</Label>
                <Select v-model="editingSubscriber.status">
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="status in statuses.filter(s => s.value !== 'all')"
                      :key="status.value"
                      :value="status.value"
                    >
                      {{ status.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <DialogFooter>
              <Button variant="ghost" @click="isEditDialogOpen = false">
                Cancel
              </Button>
              <Button @click="updateSubscriber">
                Save Changes
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        <!-- Import Dialog -->
        <Dialog v-model:open="isImportDialogOpen">
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Import Subscribers</DialogTitle>
              <DialogDescription>
                Upload a CSV or Excel file containing subscriber data.
                <button
                  @click="downloadTemplate"
                  class="text-primary-600 hover:text-primary-500"
                >
                  Download template
                </button>
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
              <div class="space-y-2">
                <Label>File</Label>
                <Input
                  type="file"
                  @change="e => importFile = e.target.files?.[0]"
                  accept=".csv,.xlsx,.xls"
                />
                <p class="text-sm text-muted-foreground">
                  Accepted formats: CSV, Excel (.xlsx, .xls)
                </p>
              </div>
            </div>

            <DialogFooter>
              <Button variant="ghost" @click="isImportDialogOpen = false">
                Cancel
              </Button>
              <Button
                :disabled="!importFile"
                @click="handleImport"
              >
                Import
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        <!-- Import Errors Modal -->
        <ImportErrorsModal
          :is-open="!!$page.props.import_errors"
          :errors="$page.props.import_errors || []"
          @close="() => delete $page.props.import_errors"
        />
      </div>
    </div>
  </AppLayout>
</template>
