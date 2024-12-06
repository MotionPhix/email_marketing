<script setup>
import { useForm } from '@inertiajs/vue3';
import {Input} from "@/Components/ui/input";
import {Button} from "@/Components/ui/button";

const { recipient } = defineProps({
  recipient: Object,
});

const form = useForm({
  audience_id: recipient.audience_id,
  email: recipient.email,
  name: recipient.name,
});
</script>

<template>
  <GlobalModal max-width="sm" panel-classes="rounded-xl bg-white">
    <h1 class="text-2xl font-bold">
      {{ recipient.uuid ? 'Edit' : 'Update' }} Recipient
    </h1>

    <form @submit.prevent="form.post(route('recipients.add'))" class="mt-4">
      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <Input
          v-model="form.name"
          type="text"
          id="name"
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>

      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <Input
          v-model="form.email"
          type="email"
          id="email"
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>

      <div class="flex justify-end pt-4">
        <Button
          type="submit"
          :disabled="form.processing">
          {{ recipient.uuid ? 'Update' : 'Save' }} Recipient
        </Button>
      </div>
    </form>
  </GlobalModal>
</template>
