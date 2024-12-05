<script setup lang="ts">
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {Button} from "@/Components/ui/button";

const modalRef = ref()

const form = useForm({
  file: null,
});

const submit = () => {
  form.post(route("recipients.import.store"), {
    onSuccess: () => modalRef.value.close(),
  });
};
</script>

<template>
  <GlobalModal v-slot="{ close }" max-width="sm" :close-button="false" ref="modalRef">

    <h1 class="text-2xl font-bold mb-4">Import Recipients</h1>

    <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label for="file" class="block text-sm font-medium text-gray-700">Upload File</label>
        <input
          id="file"
          type="file"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          @change="form.file = $event.target.files[0]"
          accept=".csv, .xls, .xlsx"
        />
        <p v-if="form.errors.file" class="text-red-500 text-sm mt-1">{{ form.errors.file }}</p>
      </div>

      <div class="flex items-center justify-between">
        <Button
          @click="close"
          type="button" variant="ghost"
          :disabled="form.processing">
          Cancel
        </Button>

        <Button
          type="submit"
          :disabled="form.processing">
          Import
        </Button>
      </div>
    </form>
  </GlobalModal>
</template>
