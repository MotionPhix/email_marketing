<script setup>
import { useForm } from '@inertiajs/vue3'
import {Button} from "@/Components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {ref} from "vue"
import InputError from "@/Components/InputError.vue";

const { recipient } = defineProps({
  recipient: Object,
});

const form = useForm({
  email: recipient.email,
  name: recipient.name,
});

const modalRef = ref()

const onSubmit = () => {
  form.put(route('recipients.update', recipient.uuid), {
    onSuccess: () => modalRef.value.close()
  })
}
</script>

<template>
  <GlobalModal ref="modalRef" v-slot="{ close }">

    <Card class="w-full">
      <CardHeader>
        <CardTitle>Update {{ form.name }}</CardTitle>
        <CardDescription>
          Update basic information for {{ form.name }}.
        </CardDescription>
      </CardHeader>

      <CardContent>
        <form>
          <div class="grid items-center w-full gap-4">

            <div class="flex flex-col space-y-1.5">
              <Label for="name">Name</Label>
              <Input id="name" v-model="form.name" placeholder="Name of your recipient" />
              <InputError :message="form.errors.name" />
            </div>

            <div class="flex flex-col space-y-1.5">
              <Label for="email">Email address</Label>
              <Input id="email" v-model="form.email" placeholder="Email of your recipient" />
              <InputError :message="form.errors.email" />
            </div>

          </div>
        </form>
      </CardContent>

      <CardFooter class="justify-end flex gap-2 px-6 pb-6">
        <Button type="button" variant="outline" @click="close">
          Cancel
        </Button>

        <Button @click="onSubmit">Update</Button>
      </CardFooter>
    </Card>

  </GlobalModal>
</template>
