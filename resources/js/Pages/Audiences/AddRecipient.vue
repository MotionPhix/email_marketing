<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useForm } from "@inertiajs/vue3"
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandItem,
  CommandList,
} from '@/Components/ui/command'
import { ScrollArea } from '@/Components/ui/scroll-area'
import { Badge } from '@/Components/ui/badge'
import { Check, Search, Users, X } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardFooter,
} from '@/Components/ui/card'
import GlobalModal from '@/Components/GlobalModal.vue'
import {toast} from "vue-sonner";

interface Recipient {
  id: number
  uuid: string
  email: string
  name: string
}

interface Audience {
  id: number
  uuid: string
  name: string
  recipients_on_audience: Recipient[]
}

interface Props {
  recipients: Recipient[]
  audience: Audience
}

const props = defineProps<Props>()
const modalRef = ref()

// Form and Search State
const searchTerm = ref('')
const isSubmitting = ref(false)

const form = useForm({
  selectedRecipients: props.audience.recipients_on_audience,
})

// Computed Properties
const filteredRecipients = computed(() => {
  const search = searchTerm.value.toLowerCase()
  if (!search) return props.recipients

  return props.recipients.filter(recipient =>
    recipient.name.toLowerCase().includes(search) ||
    recipient.email.toLowerCase().includes(search)
  )
})

const selectedCount = computed(() => form.selectedRecipients.length)

const remainingCount = computed(() =>
  props.recipients.length - form.selectedRecipients.length
)

// Methods
const close = () => {
  if (isSubmitting.value) return
  modalRef.value?.onClose()
}

const removeRecipient = (recipient: Recipient) => {
  form.selectedRecipients = form.selectedRecipients.filter(r => r.id !== recipient.id)
}

const toggleRecipient = (recipient: Recipient) => {
  const index = form.selectedRecipients.findIndex(r => r.id === recipient.id)
  if (index === -1) {
    form.selectedRecipients.push(recipient)
  } else {
    form.selectedRecipients.splice(index, 1)
  }
}

const isSelected = (recipient: Recipient) =>
  form.selectedRecipients.some(r => r.id === recipient.id)

const onSubmit = () => {
  isSubmitting.value = true
  form.put(route('audiences.merge_recipients', props.audience.uuid), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Recipients updated successfully')
      modalRef.value?.onClose()
    },
    onError: (errors) => {
      toast.error(errors.flash?.[0] || 'Failed to update recipients')
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

// Reset search when modal closes
watch(() => modalRef.value?.isOpen, (isOpen) => {
  if (!isOpen) {
    searchTerm.value = ''
  }
})
</script>

<template>
  <GlobalModal
    ref="modalRef"
    padding="p-0"
    modal-title="Manage Recipients"
    :description="`Add or remove recipients from ${audience.name}`">

    <template #header></template>

    <Card class="border-0 shadow-none py-4">
      <form @submit.prevent="onSubmit">
        <CardContent>
          <!-- Search and Selection Info -->
          <div class="mb-4 flex items-center justify-between">
            <div class="relative flex-1">
              <Search class="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                v-model="searchTerm"
                placeholder="Search recipients..."
                class="pl-8"
              />
            </div>
          </div>

          <!-- Recipients List -->
          <ScrollArea class="min-h-56">
            <Command class="rounded-lg border group">
              <CommandEmpty class="p-6 text-center">
                <div class="flex flex-col items-center gap-2">
                  <Users class="h-8 w-8 text-muted-foreground" />
                  <p class="text-sm text-muted-foreground">No recipients found</p>
                </div>
              </CommandEmpty>

              <CommandList>
                <CommandGroup>
                  <CommandItem
                    v-for="recipient in filteredRecipients"
                    :key="recipient.id"
                    :value="recipient"
                    @click="toggleRecipient(recipient)"
                    class="flex cursor-pointer items-center justify-between p-2">
                    <div class="flex flex-col">
                      <span class="font-medium">{{ recipient.name }}</span>
                      <span class="text-sm text-muted-foreground group-hover:text-muted">
                        {{ recipient.email }}
                      </span>
                    </div>

                    <Check
                      v-if="isSelected(recipient)"
                      class="h-4 w-4 text-primary"
                    />
                  </CommandItem>
                </CommandGroup>
              </CommandList>
            </Command>
          </ScrollArea>

          <!-- Selected Recipients -->
          <div v-if="selectedCount > 0" class="mt-4">
            <div class="text-sm font-medium text-muted-foreground mb-2">
              Selected Recipients
            </div>
            <div class="flex flex-wrap gap-2">
              <Badge
                v-for="recipient in form.selectedRecipients"
                :key="recipient.id"
                variant="secondary"
                class="flex items-center gap-1">
                {{ recipient.name }}
                <button
                  type="button"
                  @click="removeRecipient(recipient)"
                  class="ml-1 rounded-full hover:bg-muted">
                  <X class="h-3 w-3" />
                </button>
              </Badge>
            </div>
          </div>
        </CardContent>

        <CardFooter class="flex justify-between gap-2">
          <div class="text-sm text-muted-foreground">
            {{ remainingCount }} available
          </div>
          <div class="flex gap-2">
            <Button
              type="button"
              variant="outline"
              @click="close"
              :disabled="isSubmitting">
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="isSubmitting || selectedCount === 0">
              {{ isSubmitting ? 'Updating...' : 'Update' }}
            </Button>
          </div>
        </CardFooter>
      </form>
    </Card>
  </GlobalModal>
</template>

<style scoped>
.command-input {
  @apply w-full flex h-10 items-center space-x-1 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
</style>
