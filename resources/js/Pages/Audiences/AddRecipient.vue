<script setup lang="ts">
import {computed, ref} from 'vue'
import {Button} from '@/Components/ui/button'
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
import {Card, CardContent, CardDescription, CardFooter, CardTitle} from "@/Components/ui/card";
import {ComboboxItemIndicator} from "radix-vue";
import {useForm} from "@inertiajs/vue3";

const {recipients, audience} = defineProps<{
  recipients: object[]
  audience: object
}>()

const modalRef = ref()
const searchTerm = ref('')

const filteredRecipients = computed(() =>
  searchTerm.value === ''
    ? recipients
    : recipients.filter((recipient) => {
      return recipient.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    })
)

const form = useForm({
  selectedRecipients: audience.recipients,
})

const onSubmit = () => {
  form.put(route('audiences.merge_recipients', audience.uuid), {
    preserveScroll: true,
    onSuccess: () => modalRef.value.close(),
    onError: (error) => {
      console.log(error)
    }
  })
}
</script>

<template>
  <GlobalModal v-slot="{ close }" ref="modalRef">

    <form @submit.prevent="onSubmit">

      <Card class="p-6">
        <CardTitle>
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

            <CommandInput class="!border-none" placeholder="Search recipients..."/>
            <CommandEmpty>No recipient found</CommandEmpty>

            <CommandList
              class="h-64 overflow-y-auto scrollbar-none scroll-smooth">
              <CommandGroup class="divide-y">
                <CommandItem
                  class="py-3 grid"
                  v-for="recipient in filteredRecipients"
                  :key="recipient.id"
                  :value="recipient">

                  <span>{{ recipient.name }}</span>

                  <ComboboxItemIndicator class="flex gap-2 text-muted-foreground">
                    <span>{{ recipient.email }}</span>
                    <Check
                      :class="cn(
                        'mr-2 h-4 w-4',
                      )"
                    />
                  </ComboboxItemIndicator>

                </CommandItem>
              </CommandGroup>
            </CommandList>
          </Command>
        </CardContent>

        <CardFooter class="flex justify-between p-0">
          <Button @click="close" type="button" variant="outline">
            Cancel
          </Button>

          <Button type="submit">
            Merge {{ form.selectedRecipients[0]?.name }}
            <span v-if="form.selectedRecipients.length > 1">{{ `+ ${form.selectedRecipients.length - 1} more` }}</span>
          </Button>
        </CardFooter>
      </Card>
    </form>
  </GlobalModal>
</template>
