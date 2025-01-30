<script setup>
import {useForm} from '@inertiajs/vue3'
import {Button} from "@/Components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card'
import {Input} from '@/Components/ui/input'
import {Label} from '@/Components/ui/label'
import {ref} from "vue"
import InputError from "@/Components/InputError.vue";

const {recipient} = defineProps({
  recipient: Object,
});

const form = useForm({
  email: recipient.email,
  name: recipient.name,
});

const quickFormRef = ref()

const close = () => {
  quickFormRef.value.onClose()
}

const onSubmit = () => {
  form.put(route('recipients.update', recipient.uuid), {
    onSuccess: () => quickFormRef.value.onClose()
  })
}
</script>

<template>
  <GlobalModal
    ref="quickFormRef">

    <CardHeader class="px-0 pt-0">
      <CardTitle>Update {{ form.name }}</CardTitle>
      <CardDescription>
        Update basic information for {{ form.name }}.
      </CardDescription>
    </CardHeader>

    <CardContent class="p-0">
      <form>
        <div>
          <FormField
            label="Name"
            :error="form.errors.name"
            v-model="form.name"
            placeholder="Name of your recipient"
          />
        </div>

        <div>
          <FormField
            label="Email address"
            v-model="form.email"
            :error="form.errors.email"
            placeholder="Email of your recipient"
          />
        </div>
      </form>
    </CardContent>

    <CardFooter class="justify-end flex gap-2 p-0">

      <Button type="button" variant="outline" @click="close">
        Cancel
      </Button>

      <Button @click="onSubmit">Update</Button>

    </CardFooter>

  </GlobalModal>
</template>
