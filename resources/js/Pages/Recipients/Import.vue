<script setup lang="ts">
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import {Button} from "@/Components/ui/button";
import InputError from "@/Components/InputError.vue";
import {XIcon, FileTextIcon} from "lucide-vue-next";

const importRecipientsRef = ref()

const form = useForm({
  file: null,
  progress: 0
});

const submit = () => {
  form.post(route("recipients.import.store"), {
    onSuccess: () => importRecipientsRef.value.onClose(),
    onProgress: (e) => {
      form.progress = Math.round((e.loaded / e.total) * 100);
    }
  });
};

const close = () => {
  importRecipientsRef.value.onClose()
}

const removeFile = () => { form.file = null; };

const formatFileSize = (size) => { const units = ["B", "KB", "MB", "GB", "TB"]; let unitIndex = 0; let fileSize = size; while (fileSize >= 1024 && unitIndex < units.length - 1) { fileSize /= 1024; unitIndex++; } return `${fileSize.toFixed(2)} ${units[unitIndex]}`; };
</script>

<template>
  <GlobalModal ref="importRecipientsRef">

    <h1 class="text-2xl font-thin mb-4">Import Recipients</h1>

    <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
      <div v-if="! form.file" class="grid">
        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-36 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
          <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
            </svg>
            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
              <span class="font-semibold">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              .CSV, .XLS, or .XLSX files only (MAX. 10MB)
            </p>
          </div>

          <input
            accept=".csv, .xls, .xlsx"
            @change="form.file = $event.target.files[0]"
            type="file" class="hidden"
            id="dropzone-file"  />
        </label>

        <InputError :message="form.errors.file" />
      </div>

      <div v-else class="flex flex-col items-center">
        <div class="w-full grid gap-1 mb-4">
          <div class="flex items-center justify-between gap-2">
            <div class="flex gap-2">
              <FileTextIcon />

              <div class="grid gap-1">
                <h4 class="first-letter:maz-capitalize dark:text-muted-foreground text-sm font-normal leading-snug">
                  {{ `${form.file.name} ${formatFileSize(form.file.size)}` }}
                </h4>
                <h5 class="text-gray-400 text-xs font-normal leading-[18px]">
                  File ready. Click <strong>Import</strong> to upload.
                </h5>
              </div>
            </div>

            <Button
              @click="removeFile"
              type="button"
              variant="secondary"
              size="icon"
              class="w-4 h-4 p-0.5"
              :disabled="form.processing">
              <XIcon />
            </Button>
          </div>
        </div>

        <div
          class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
          <div
            class="bg-blue-600 h-2.5 rounded-full"
            :style="{ width: form.progress + '%' }" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-x-2">
        <Button
          @click="close"
          type="button" variant="ghost"
          :disabled="form.processing">
          Cancel
        </Button>

        <Button
          type="submit"
          :disabled="!form.file || form.processing">
          Import
        </Button>
      </div>
    </form>
  </GlobalModal>
</template>
