<script setup>
import { useForm } from '@inertiajs/vue3';

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
  <div>
    <h1 class="text-2xl font-bold">Add Recipient</h1>

    <form @submit.prevent="form.post(route('recipients.add'))" class="mt-4">
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input
          v-model="form.email"
          type="email"
          id="email"
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>

      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input
          v-model="form.name"
          type="text"
          id="name"
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>

      <button
        type="submit"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        :disabled="form.processing">
        Add Recipient
      </button>
    </form>
  </div>
</template>
