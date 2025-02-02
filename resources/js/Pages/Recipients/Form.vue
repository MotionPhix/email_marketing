<script setup lang="ts">
import {useForm} from '@inertiajs/vue3';
import {ref} from "vue";
import {toast} from "vue-sonner";

const props = defineProps<{
  recipient: {
    email: string
    gender: string
    name: string
    current_status: string
    uuid?: string
  },
}>();

const recipientsModalRef = ref()

const form = useForm({
  email: props.recipient.email,
  name: props.recipient.name,
  gender: props.recipient.gender,
  status: props.recipient.current_status,
});

const onSubmit = () => {
  if (props.recipient.uuid) {
    form.put(route('recipients.update', props.recipient.uuid), {
      onSuccess: () => {
        toast.success('Recipient was updated successfully!')
        recipientsModalRef.value.onClose()
      },
      onError: (errors) => {
        console.log(errors)
      }
    })
  } else {
    form.post(route('recipients.store'), {
      onSuccess: () => {
        toast.success('Recipient was added successfully!')
        recipientsModalRef.value.onClose()
      },
      onError: (errors) => {
        console.log(errors)
      }
    })
  }
}

const close = () => {
  recipientsModalRef.value.onClose()
}
</script>

<template>
  <GlobalModal
    ref="recipientsModalRef">
    <h1 class="text-2xl font-bold">
      {{ recipient.uuid ? `Edit ${recipient.name}` : 'New recipient' }}
    </h1>

    <form @submit.prevent="onSubmit" class="mt-4">
      <div class="mb-4 grid gap-2">
        <FormField
          v-model="form.name"
          placeholder="Enter recipient name"
          :error="form.errors.name"
          label="Name"
        />
      </div>

      <div class="mb-4 gap-2">
        <FormField
          v-model="form.email"
          placeholder="Enter recipient email"
          :error="form.errors.email"
          type="email"
          label="Email"
        />
      </div>

      <section class="grid sm:grid-cols-2 gap-2">

        <div class="mb-4 gap-2">
          <FormField
            label="Gender"
            placeholder="Select gender"
            :error="form.errors.gender"
            v-model="form.gender"
            type="select"
            :options="[
              { value: 'male', label: 'Male' },
              { value: 'female', label: 'Female' }
            ]"
          />
        </div>

        <div class="mb-4 gap-2">
          <FormField
            type="select"
            disabled
            :error="form.errors.status"
            placeholder="Set status"
            v-model="form.status"
            label="Status"
            :options="[
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Dormant' },
              { value: 'banned', label: 'Blacklisted' },
              { value: 'unsubscribed', label: 'Opted out' },
              { value: 'new', label: 'New' }
            ]"
          />
        </div>

      </section>

      <div class="flex justify-end gap-4">
        <Button
          type="button"
          variant="outline"
          :disabled="form.processing"
          @click="close">
          Cancel
        </Button>

        <Button
          type="submit"
          :disabled="form.processing">
          {{ recipient.uuid ? 'Update' : 'Add' }} recipient
        </Button>
      </div>
    </form>
  </GlobalModal>
</template>
