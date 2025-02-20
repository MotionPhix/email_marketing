<script setup lang="ts">
import {useForm} from '@inertiajs/vue3'
import {ref} from "vue";

interface MailingList {
  id: number
  name: string
  is_default: boolean
  description?: string
  settings?: {}
}

const props = defineProps<{
  list: MailingList
}>()

const mailingListModal = ref()

const form = useForm({
  name: props.list.name || '',
  description: props.list.description || '',
  is_default: props.list.is_default,
  settings: props.list.settings || {}
})

const submit = () => {
  props.list.id
    ? form.put(route('mailing-lists.update', props.list.id), {
      preserveScroll: true,
      onSuccess: () => {
        mailingListModal.value.close()
      },
    })
    : form.post(route('mailing-lists.store'), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        mailingListModal.value.close()
      },
    })
}
</script>

<template>
  <GlobalModal
    ref="mailingListModal"
    modal-title="Create Mailing List"
    description="Create a new list to organize your subscribers.">
    <form>
      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <FormField
            label="Name"
            v-model="form.name"
            type="text"
            placeholder="Newsletter Subscribers"
            :error="form.errors.name"
          />
        </div>

        <div class="grid gap-2">
          <FormField
            label="Description"
            type="textarea"
            v-model="form.description"
            placeholder="Main list for newsletter subscribers"
            :error="form.errors.description"
          />
        </div>

        <div class="flex items-center space-x-2">
          <Checkbox
            id="is_default"
            v-model="form.is_default"
          />
          <Label for="is_default">Make this the default list for new subscribers</Label>
        </div>
      </div>
    </form>

    <template #footer>
      <Button
        type="button"
        variant="ghost"
        @click="mailingListModal.close">
        Cancel
      </Button>

      <Button
        type="button"
        @click="submit"
        :disabled="form.processing">
        Create List
      </Button>
    </template>
  </GlobalModal>
</template>
