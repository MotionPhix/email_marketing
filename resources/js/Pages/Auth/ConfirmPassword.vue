<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const form = useForm({
  password: '',
})

const submit = () => {
  form.post(route('password.confirm'), {
    onFinish: () => {
      form.reset()
    },
  })
}
</script>

<template>
  <Head title="Confirm Password" />

  <AuthenticationCard>
    <template #logo>
      <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
    </template>

    <div class="mb-4 text-sm text-muted-foreground">
      This is a secure area of the application. Please confirm your password before continuing.
    </div>

    <form @submit.prevent="submit">
      <div>
        <FormField
          label="Password"
          v-model="form.password"
          type="password"
          required
          placeholder="Enter your password"
          autofocus
          :message="form.errors.password"
        />
      </div>

      <div class="mt-4 flex justify-end">
        <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Confirm
        </Button>
      </div>
    </form>
  </AuthenticationCard>
</template>
