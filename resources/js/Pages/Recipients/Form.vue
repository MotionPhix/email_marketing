<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {Input} from "@/Components/ui/input";
import {Button} from "@/Components/ui/button";
import {Select, SelectTrigger, SelectValue} from "@/Components/ui/select/index.js";
import {SelectContent, SelectItem} from "@/Components/ui/select";
import {ref} from "vue";
import InputError from "@/Components/InputError.vue";

const { recipient } = defineProps<{
  recipient: {
    email: string
    gender: string
    name: string
    status: string
    uuid?: string
  },
}>();

const modalRef = ref()

const form = useForm({
  email: recipient.email,
  name: recipient.name,
  gender: recipient.gender,
  status: recipient.status,
});

const onSubmit = () => {
  if (recipient.uuid) {
    form.put(route('recipients.update', recipient.uuid), {
      onSuccess: () => {
        console.log('Recipient updated')
        modalRef.value.close()
      },
      onError: (errors) => {
        console.log(errors)
      }
    })
  } else {
    form.post(route('recipients.store'), {
      onSuccess: () => {
        console.log('Recipient added')
        modalRef.value.close()
      },
      onError: (errors) => {
        console.log(errors)
      }
    })
  }
}
</script>

<template>
  <GlobalModal max-width="md" panel-classes="rounded-xl bg-white" ref="modalRef">
    <h1 class="text-2xl font-bold">
      {{ recipient.uuid ? `Edit ${recipient.name}` : 'New recipient' }}
    </h1>

    <form @submit.prevent="onSubmit" class="mt-4">
      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <Input
          v-model="form.name"
          placeholder="Enter recipient name"
          type="text"
          id="name"
        />

        <InputError :message="form.errors.name" />
      </div>

      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <Input
          v-model="form.email"
          placeholder="Enter recipient email"
          type="email"
          id="email"
        />

        <InputError :message="form.errors.email" />
      </div>

      <section class="grid sm:grid-cols-2 gap-2">

        <div class="mb-4">
          <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
          <Select
            v-model="form.gender"
            id="gender">
            <SelectTrigger>
              <SelectValue placeholder="Select gender" />
            </SelectTrigger>

            <SelectContent>
              <SelectItem value="male">
                Male
              </SelectItem>
              <SelectItem value="female">
                Female
              </SelectItem>
              <SelectItem value="unspecified">
                Unknown
              </SelectItem>
            </SelectContent>
          </Select>

          <InputError :message="form.errors.gender" />
        </div>

        <div class="mb-4">
          <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
          <Select
            v-model="form.status"
            id="status">
            <SelectTrigger>
              <SelectValue placeholder="Set status" />
            </SelectTrigger>

            <SelectContent>
              <SelectItem value="active">
                Active
              </SelectItem>
              <SelectItem value="inactive">
                Dormant
              </SelectItem>
              <SelectItem value="banned">
                Blacklisted
              </SelectItem>
              <SelectItem value="unsubscribed">
                Opted out
              </SelectItem>
            </SelectContent>
          </Select>

          <InputError :message="form.errors.status" />
        </div>

      </section>

      <div class="flex justify-end pt-4">
        <Button
          type="submit"
          :disabled="form.processing">
          {{ recipient.uuid ? 'Update' : 'Add' }} recipient
        </Button>
      </div>
    </form>
  </GlobalModal>
</template>
