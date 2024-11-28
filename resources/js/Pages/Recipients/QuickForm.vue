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

const { recipient } = defineProps({
  recipient: Object,
});

const form = useForm({
  email: recipient.email,
  name: recipient.name,
});

const modalRef = ref()

const onSubmit = () => {
  form.put(route('recipients.update', recipient), {
    onSuccess: () => modalRef.value.close()
  })
}
</script>

<template>
  <GlobalModal ref="modalRef" v-slot="{ close }">
<!--    <h1 class="text-2xl font-bold">Edit Recipient</h1>-->

<!--    <form @submit.prevent="onSubmit" class="mt-4">-->

<!--      <div class="mb-4">-->
<!--        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>-->
<!--        <input-->
<!--          v-model="form.name"-->
<!--          type="text"-->
<!--          id="name"-->
<!--          class="mt-1 block w-full border-gray-300 rounded-md"-->
<!--        />-->
<!--      </div>-->

<!--      <div class="mb-4">-->
<!--        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>-->
<!--        <input-->
<!--          v-model="form.email"-->
<!--          type="email"-->
<!--          id="email"-->
<!--          class="mt-1 block w-full border-gray-300 rounded-md"-->
<!--        />-->
<!--      </div>-->

<!--      <Button-->
<!--        type="submit"-->
<!--        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"-->
<!--        :disabled="form.processing">-->
<!--        Update Recipient-->
<!--      </Button>-->
<!--    </form>-->
    <Card class="w-full">
      <CardHeader>
        <CardTitle>Update recipient</CardTitle>
        <CardDescription>
          Update basic recepient infomation.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="onSubmit">
          <div class="grid items-center w-full gap-4">
            <div class="flex flex-col space-y-1.5">
              <Label for="name">Name</Label>
              <Input id="name" v-model="form.name" placeholder="Name of your recipient" />
            </div>

            <div class="flex flex-col space-y-1.5">
              <Label for="email">Email address</Label>
              <Input id="email" v-model="form.email" placeholder="Email of your recipient" />
            </div>
          </div>
        </form>
      </CardContent>

      <CardFooter class="flex justify-between px-6 pb-6">
        <Button type="button" variant="outline" @click="close">
          Cancel
        </Button>

        <Button type="submit">Save</Button>
      </CardFooter>
    </Card>
  </GlobalModal>
</template>
