<script setup>
import {useForm} from '@inertiajs/vue3';

const { audience } = defineProps({
  audience: Object
})

const form = useForm({
  name: audience.name,
  description: audience.description,
});
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold">Create Audience</h1>
    <form @submit.prevent="form.post(route('audiences.store'))" class="mt-4">
      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input
          v-model="form.name"
          type="text"
          id="name"
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>

      <div class="mb-4">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>

        <textarea
          v-model="form.description"
          id="description"
          class="mt-1 block w-full border-gray-300 rounded-md" />
      </div>

      <button
        type="submit"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        :disabled="form.processing">
        Create Audience
      </button>
    </form>
  </div>
</template>
