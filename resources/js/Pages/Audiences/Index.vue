<script setup lang="ts">
import {ref, computed} from 'vue'
import AppLayout from "@/Layouts/AppLayout.vue"
import {Avatar, AvatarFallback, AvatarImage} from "@/Components/ui/avatar"
import {router, Link} from "@inertiajs/vue3"
import {
  FileTextIcon,
  XIcon,
  PenIcon,
  Trash2Icon,
  UserIcon,
  EllipsisIcon,
  PlusCircle,
  UserPlus,
  SearchIcon,
  UsersIcon,
  SortAscIcon,
} from 'lucide-vue-next'
import PageTitle from "@/Components/PageTitle.vue"
import EmptyState from "@/Components/EmptyState.vue"
import {Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription} from "@/Components/ui/dialog"
import {Badge} from "@/Components/ui/badge"
import {ScrollArea} from "@/Components/ui/scroll-area"
import {toast} from "vue-sonner";
import {useDeviceDetection} from "@/composables/useDeviceDetection";

// Types
interface Recipient {
  id: number
  uuid: string
  name: string
  email: string
}

interface Audience {
  id: number
  uuid: string
  name: string
  description: string
  recipients: Recipient[]
  recipients_count: number
  remaining_recipients_count: number
}

interface Props {
  audiences: {
    data: Audience[]
    total: number
  }
}

const props = defineProps<Props>()

// State
const searchQuery = ref('')
const showDeleteDialog = ref(false)
const audienceToDelete = ref<Audience | null>(null)
const isLoading = ref(false)
const {isMobile} = useDeviceDetection()
const sortBy = ref<'name' | 'recipients_count'>('name')
const sortOrder = ref<'asc' | 'desc'>('asc')

// Computed
const filteredAudiences = computed(() => {
  let filtered = [...props.audiences.data]

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(audience =>
      audience.name.toLowerCase().includes(query) ||
      audience.description?.toLowerCase().includes(query)
    )
  }

  filtered.sort((a, b) => {
    const modifier = sortOrder.value === 'asc' ? 1 : -1
    if (sortBy.value === 'name') {
      return a.name.localeCompare(b.name) * modifier
    }
    return (a.recipients_count - b.recipients_count) * modifier
  })

  return filtered
})

// Methods
const confirmDelete = (audience: Audience) => {
  audienceToDelete.value = audience
  showDeleteDialog.value = true
}

const handleDelete = async () => {
  if (!audienceToDelete.value) return

  try {
    await router.delete(route('audiences.destroy', audienceToDelete.value.uuid), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Audience was deleted successfully!')
        showDeleteDialog.value = false
        audienceToDelete.value = null
      },
      onError: (errors) => {
        toast.error(errors.flash?.[0] || 'Failed to delete audience')
      }
    })
  } catch (error) {
    toast.error('An unexpected error occurred')
  }
}

const removeRecipient = async (audience: Audience, recipient: Recipient) => {
  try {
    await router.delete(route('audiences.remove_recipient', {
      audience: audience.uuid,
      recipient: recipient.uuid
    }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Recipient removed successfully')
      }
    })
  } catch (error) {
    toast.error('Failed to remove recipient')
  }
}

const toggleSort = (field: typeof sortBy.value) => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortOrder.value = 'asc'
  }
}
</script>

<template>
  <AppLayout title="Audiences">
    <template #header>
      <PageTitle title="Audience List"/>
    </template>

    <template #action>
      <div class="flex items-center gap-2">
        <div class="relative w-full sm:w-64">
          <SearchIcon class="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"/>
          <Input
            v-model="searchQuery"
            placeholder="Search audiences..."
            class="pl-8"
          />
        </div>

        <Button
          variant="outline"
          size="icon"
          @click="toggleSort(sortBy)">
          <SortAscIcon
            class="h-4 w-4"
            :class="{ 'rotate-180': sortOrder === 'desc' }"
          />
        </Button>

        <GlobalLink
          as="Button"
          :size="isMobile ? 'icon' : 'default'"
          v-if="filteredAudiences.length"
          :href="route('audiences.create')">
          <PlusCircle/>
          <span v-if="!isMobile">New Audience</span>
        </GlobalLink>
      </div>
    </template>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <!-- Empty State -->
      <div v-if="!props.audiences.total" class="max-w-7xl mx-auto">
        <EmptyState
          title="Create your first audience"
          description="Start organizing your recipients into targeted groups"
          :icon="UsersIcon">
          <template #action>
            <Button as-child :href="route('audiences.create')">
              <GlobalLink as="button">
                Create Audience
              </GlobalLink>
            </Button>
          </template>
        </EmptyState>
      </div>

      <!-- Audience Grid -->
      <div
        v-else
        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="audience in filteredAudiences"
          :key="audience.id"
          class="group relative transition-all">
          <!-- Audience Header -->
          <div class="mb-4">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="font-semibold tracking-tight">
                  {{ audience.name }}
                </h3>

                <p class="text-sm text-muted-foreground line-clamp-2">
                  {{ audience.description || 'No description available.' }}
                </p>
              </div>

              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="opacity-0 group-hover:opacity-100">
                    <EllipsisIcon class="h-4 w-4"/>
                  </Button>
                </DropdownMenuTrigger>

                <DropdownMenuContent align="end" side-offset="-3">
                  <DropdownMenuGroup>
                    <GlobalLink
                      as="DropdownMenuItem"
                      :href="route('audiences.add_recipient', audience.uuid)">
                      <UserPlus class="mr-2 h-4 w-4"/>
                      Manage Recipients
                    </GlobalLink>

                    <GlobalLink
                      as="DropdownMenuItem"
                      :href="route('audiences.edit', audience.uuid)">
                      <PenIcon class="mr-2 h-4 w-4"/>
                      Edit Audience
                    </GlobalLink>

                    <DropdownMenuItem as-child>
                      <Link
                        as="button" class="w-full text-left space-x-2"
                        :href="route('audiences.show', audience.uuid)">
                        <FileTextIcon class="mr-2 h-4 w-4"/>
                        Audience Details
                      </Link>
                    </DropdownMenuItem>
                  </DropdownMenuGroup>

                  <DropdownMenuSeparator/>

                  <DropdownMenuItem
                    class="text-destructive"
                    @click="confirmDelete(audience)">
                    <Trash2Icon class="mr-2 h-4 w-4"/>
                    Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>

            <div class="mt-2 flex items-center gap-2">
              <Badge variant="secondary">
                {{ audience.recipients_count }} Recipients
              </Badge>
            </div>
          </div>

          <!-- Recipients List -->
          <ScrollArea class="h-[200px] rounded-md border p-4">
            <div v-if="audience.recipients.length" class="space-y-4">
              <div
                v-for="recipient in audience.recipients"
                :key="recipient.id"
                class="group flex items-center justify-between">
                <div class="flex items-center gap-2 relative">
                  <Avatar class="h-8 w-8">
                    <AvatarImage
                      :src="`https://avatar.vercel.sh/${recipient.email}.png`"
                      alt={recipient.name}
                    />
                    <AvatarFallback>
                      {{ recipient.name.charAt(0) }}
                    </AvatarFallback>
                  </Avatar>

                  <div class="space-y-1">
                    <p class="text-sm font-medium leading-none">
                      {{ recipient.name }}
                    </p>

                    <p class="text-xs text-muted-foreground">
                      {{ recipient.email }}
                    </p>
                  </div>
                </div>

                <div class="opacity-0 group-hover:opacity-100 flex-1 absolute right-3 top-2">
                  <Button
                    variant="ghost"
                    size="icon" class="shrink-0 h-5 w-5"
                    @click="removeRecipient(audience, recipient)">
                    <XIcon class="h-4 w-4"/>
                  </Button>
                </div>
              </div>

              <div
                v-if="audience.remaining_recipients_count"
                class="pt-2 text-center text-sm text-muted-foreground">
                + {{ audience.remaining_recipients_count }} more recipients
              </div>
            </div>

            <div
              v-else
              class="flex h-full flex-col items-center justify-center gap-2">
              <UserIcon class="h-8 w-8 text-muted-foreground"/>
              <p class="text-sm text-muted-foreground">No recipients yet</p>
              <Button
                as-child
                variant="outline"
                size="sm">
                <GlobalLink
                  :href="route('audiences.add_recipient', audience.uuid)">
                  Add Recipients
                </GlobalLink>
              </Button>
            </div>
          </ScrollArea>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>Delete Audience</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete "{{ audienceToDelete?.name }}"?
            This action cannot be undone.
          </DialogDescription>
        </DialogHeader>

        <div class="flex justify-end gap-2">
          <Button
            variant="outline"
            @click="showDeleteDialog = false">
            Cancel
          </Button>
          <Button
            variant="destructive"
            :disabled="isLoading"
            @click="handleDelete">
            Delete
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

.group-hover\/item\:opacity-100:hover {
  opacity: 1;
}
</style>
