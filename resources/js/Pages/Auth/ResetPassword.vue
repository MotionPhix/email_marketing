<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticationCard from '@/Components/AuthenticationCard.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const props = defineProps<{
  email: string
  token: string
}>()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <Head title="Reset Password" />

  <AuthenticationCard>
    <template #logo>
      <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
    </template>

    <form @submit.prevent="submit">
      <div>
        <FormField
          label="Email"
          v-model="form.email"
          type="email"
          placeholder="Enter your email address"
          required
          autofocus
          :error="form.errors.email"
        />
      </div>

      <div class="mt-4">
        <FormField
          label="Password"
          v-model="form.password"
          type="password"
          placeholder="Enter your new password"
          required
          :error="form.errors.password"
        />
      </div>

      <div class="mt-4">
        <FormField
          label="Confirm Password"
          v-model="form.password_confirmation"
          type="password"
          required
          placeholder="Confirm your new password"
          :error="form.errors.password_confirmation"
        />
      </div>

      <div class="mt-4 flex items-center justify-end">
        <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Reset Password
        </Button>
      </div>
    </form>
  </AuthenticationCard>
</template>
