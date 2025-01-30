<script setup lang="ts">
import {computed, ref} from 'vue'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/Components/ui/command'
import {cn} from '@/lib/utils'
import {Check} from 'lucide-vue-next'
import Form from "@/Pages/Templates/Form.vue";
import {ComboboxItemIndicator} from "radix-vue";
import {useForm} from "@inertiajs/vue3";

const props = defineProps<{
  recipients: Array<any>
  audience: object
}>()

const addRecipientRef = ref()
const searchTerm = ref('')

const filteredRecipients = computed(() =>
  searchTerm.value === ''
    ? props.recipients
    : props.recipients.filter((recipient) => {
      return recipient.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    })
)

const form = useForm({
  selectedRecipients: props.audience.recipients,
})

const close = () => {
  addRecipientRef.value.onClose()
}

const onSubmit = () => {
  form.put(route('audiences.merge_recipients', props.audience.uuid), {
    preserveScroll: true,
    onSuccess: () => addRecipientRef.value.onClose(),
    onError: (error) => {
      console.log(error)
    }
  })
}
</script>

<template>
  <GlobalModal
    ref="addRecipientRef"
    :close-button="false">

    <form @submit.prevent="onSubmit">
      <CardTitle class="capitalize">
        {{ audience.name }} Recipients
      </CardTitle>

      <CardDescription>
        Add/remove recipients on <strong>{{ audience.name }}</strong> audience
      </CardDescription>

      <CardContent class="p-0 py-6">
        <Command
          multiple
          :display-value="(v) => v.name"
          v-model="form.selectedRecipients"
          v-model:searchTerm="searchTerm">

          <CommandInput
            placeholder="Search recipients..."
          />

          <CommandEmpty>No recipient found</CommandEmpty>

          <CommandList
            class="h-64 overflow-y-auto scrollbar-none scroll-smooth mt-4">
            <CommandGroup>
              <CommandItem
                class="py-3 grid rounded-md somber"
                v-for="recipient in filteredRecipients"
                :key="recipient.id"
                :value="recipient">

                <span>{{ recipient.name }}</span>

                <ComboboxItemIndicator
                  class="flex justify-between gap-2 text-muted-foreground">
                  <span>{{ recipient.email }}</span>
                  <Check
                    :class="cn(
                        'mr-2 h-5 w-5',
                      )"
                  />
                </ComboboxItemIndicator>

              </CommandItem>
            </CommandGroup>
          </CommandList>
        </Command>
      </CardContent>

      <CardFooter class="flex justify-end gap-2 p-0">
        <Button @click="close" type="button" variant="outline">
          Cancel
        </Button>

        <Button type="submit">
          Merge {{ form.selectedRecipients[0]?.name }}
          <span v-if="form.selectedRecipients.length > 1">{{ `+ ${form.selectedRecipients.length - 1} more` }}</span>
        </Button>
      </CardFooter>

    </form>
  </GlobalModal>
</template>
