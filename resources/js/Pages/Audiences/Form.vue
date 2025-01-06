<script setup>
import {useForm} from '@inertiajs/vue3';
import {Button} from "@/Components/ui/button";
import MazTextarea from "maz-ui/components/MazTextarea";
import InputError from "@/Components/InputError.vue";
import {computed} from "vue";
import {Label} from "@/Components/ui/label";
import {Input} from "@/Components/ui/input";

const {audience} = defineProps({
  audience: Object
})

const action = computed(() => audience.uuid ? 'Update' : 'Create')

const form = useForm({
  name: audience.name,
  description: audience.description,
});
</script>

<template>
  <GlobalModal
    ref="modalRef"
    :close-button="false"
    v-slot="{ close }" max-width="md"
    panel-classes="rounded-xl bg-gray-100 dark:bg-gray-700">
    <h1 class="text-2xl font-bold">
      {{ audience.uuid ? `Edit ${audience.name}` : 'New' }}
    </h1>

    <form
      class="mt-4"
      @submit.prevent="audience.uuid
      ? form.put(route('audiences.update', audience.uuid), {
        onSuccess: () => close()
      })
      : form.post(route('audiences.store'), {
        onSuccess: () => close()
      })">
      <div class="mb-4 grid gap-2">
        <Label for="name">Name</Label>

        <Input
          v-model="form.name"
          placeholder="Give the audience a name"
          class="dark:text-gray-800"
          type="text"
          id="name"
        />

        <InputError :message="form.errors.name"/>
      </div>

      <div class="mb-4 grid gap-2">
        <Label
          for="description">
          Description
        </Label>

        <MazTextarea
          v-model="form.description"
          placeholder="Describe the audience"
          class="dark:text-gray-800"
          id="description"
          roundedSize="sm"/>
      </div>

      <div class="flex justify-end gap-4 pt-6">
        <Button
          type="button"
          @click="close"
          variant="ghost"
          :disabled="form.processing">
          Cancel
        </Button>

        <Button
          type="submit"
          :loading="form.processing"
          :disabled="form.processing">
          {{ action }}
        </Button>
      </div>
    </form>
  </GlobalModal>
</template>
