<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useToast } from '@/hooks/useToast'

const { toast } = useToast()

const props = defineProps<{
  segments: {
    data: Array<{
      id: number
      name: string
      description: string | null
      subscriber_count: number
      created_at: string
      conditions: Array<{
        field: string
        operator: string
        value: string
      }>
    }>
    total: number
  }
}>()

const showCreateModal = ref(false)
const editingSegment = ref<number | null>(null)

const form = useForm({
  name: '',
  description: '',
  conditions: [{
    field: 'email',
    operator: 'contains',
    value: ''
  }]
})

const addCondition = () => {
  form.conditions.push({
    field: 'email',
    operator: 'contains',
    value: ''
  })
}

const removeCondition = (index: number) => {
  form.conditions.splice(index, 1)
}

const submitForm = () => {
  if (editingSegment.value) {
    form.put(route('segments.update', editingSegment.value), {
      onSuccess: () => {
        showCreateModal.value = false
        toast({
          title: "Success",
          description: "Segment updated successfully"
        })
      }
    })
  } else {
    form.post(route('segments.store'), {
      onSuccess: () => {
        showCreateModal.value = false
        toast({
          title: "Success",
          description: "Segment created successfully"
        })
      }
    })
  }
}
</script>

<template>
  <AppLayout title="Segments">
    <Head title="Segments" />

    <div class="container space-y-6 p-4 md:p-8">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-3xl font-bold tracking-tight">Segments</h2>
          <p class="text-muted-foreground">
            Create and manage subscriber segments
          </p>
        </div>

        <Button @click="showCreateModal = true">
          <PlusIcon class="h-4 w-4 mr-2" />
          Create Segment
        </Button>
      </div>

      <!-- Segments Grid -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="segment in segments.data" :key="segment.id">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              {{ segment.name }}
              <DropdownMenu>
                <DropdownMenuTrigger as="button" class="icon-button">
                  <MoreHorizontalIcon class="h-4 w-4" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="editSegment(segment)">
                    <PencilIcon class="h-4 w-4 mr-2" />
                    Edit
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    @click="deleteSegment(segment.id)"
                    class="text-destructive"
                  >
                    <TrashIcon class="h-4 w-4 mr-2" />
                    Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </CardTitle>
            <CardDescription>
              {{ segment.description || 'No description' }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">Subscribers</span>
                <Badge>{{ segment.subscriber_count }}</Badge>
              </div>
              <Separator />
              <div class="space-y-2">
                <p class="text-sm font-medium">Conditions:</p>
                <div v-for="(condition, index) in segment.conditions" :key="index" class="text-sm">
                  <Badge variant="secondary" class="mr-2">
                    {{ condition.field }}
                  </Badge>
                  {{ condition.operator }}
                  <Badge variant="outline" class="ml-2">
                    {{ condition.value }}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Create/Edit Modal -->
      <Dialog :open="showCreateModal" @update:open="showCreateModal = false">
        <DialogContent class="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>
              {{ editingSegment ? 'Edit Segment' : 'Create Segment' }}
            </DialogTitle>
          </DialogHeader>

          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="space-y-4">
              <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  placeholder="Newsletter Subscribers"
                  :error="form.errors.name"
                />
              </div>

              <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  placeholder="Subscribers who opted in to the newsletter"
                  :error="form.errors.description"
                />
              </div>

              <div class="space-y-4">
                <Label>Conditions</Label>
                <div
                  v-for="(condition, index) in form.conditions"
                  :key="index"
                  class="flex items-center gap-2"
                >
                  <Select v-model="condition.field" class="w-1/3">
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="email">Email</SelectItem>
                      <SelectItem value="status">Status</SelectItem>
                      <SelectItem value="company">Company</SelectItem>
                      <SelectItem value="created_at">Date Added</SelectItem>
                    </SelectContent>
                  </Select>

                  <Select v-model="condition.operator" class="w-1/3">
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="contains">Contains</SelectItem>
                      <SelectItem value="equals">Equals</SelectItem>
                      <SelectItem value="starts_with">Starts with</SelectItem>
                      <SelectItem value="ends_with">Ends with</SelectItem>
                    </SelectContent>
                  </Select>

                  <Input
                    v-model="condition.value"
                    class="w-1/3"
                    placeholder="Value"
                  />

                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    @click="removeCondition(index)"
                    :disabled="form.conditions.length === 1"
                  >
                    <XIcon class="h-4 w-4" />
                  </Button>
                </div>

                <Button
                  type="button"
                  variant="outline"
                  @click="addCondition"
                  class="w-full"
                >
                  <PlusIcon class="h-4 w-4 mr-2" />
                  Add Condition
                </Button>
              </div>
            </div>

            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                @click="showCreateModal = false"
              >
                Cancel
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ editingSegment ? 'Update' : 'Create' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
