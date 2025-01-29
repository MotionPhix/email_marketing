<script setup>
import {useForm} from '@inertiajs/vue3';
import {Button} from "@/Components/ui/button";
import {computed, ref} from "vue";
import {Separator} from "@/Components/ui/separator";

const props = defineProps({
  audience: Object
})

const audienceForm = ref()
const action = computed(() => props.audience.uuid ? 'Update' : 'Create')

const form = useForm({
  name: props.audience.name,
  description: props.audience.description,
});

const close = () => {
  audienceForm.value.onClose()
}
</script>

<template>
  <GlobalModal
    ref="audienceForm"
    :close-button="false">
    <CardTitle>
      <h1 class="text-2xl font-bold">
        {{ audience.uuid ? `Edit ${audience.name}` : 'New audience' }}
      </h1>
    </CardTitle>

    <Separator class="my-4" />

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
        <FormField
          label="Audience name"
          v-model="form.name"
          placeholder="Give the audience a name"
          :error="form.errors.name"
        />
      </div>

      <div class="mb-4 grid gap-2">
        <FormField
          label="Description"
          v-model="form.description"
          placeholder="Describe the audience"
          type="textarea"
        />
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
