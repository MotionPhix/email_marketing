<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import {IconBrandGoogle, IconBrandGithub, IconLoader} from "@tabler/icons-vue";
import InputError from "@/Components/InputError.vue";

const form = useForm({
  email: '',
  password: '',
  remember: false
})

const isLoading = ref(false)

const submit = () => {
  isLoading.value = true
  form.post(route('login'), {
    onFinish: () => {
      isLoading.value = false
    },
  })
}
</script>

<template>
  <AuthLayout>
    <Head title="Sign in" />

    <div class="lg:p-8">
      <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
        <div class="flex flex-col space-y-2 text-center">
          <h1 class="text-2xl font-semibold tracking-tight">Welcome back</h1>
          <p class="text-sm text-muted-foreground">
            Enter your email to sign in to your account
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div class="space-y-4">
            <FormField>
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="m@example.com"
                :disabled="isLoading"
                autocomplete="email"
                required
              />
              <InputError :message="form.errors.email" />
            </FormField>

            <FormField>
              <div class="flex items-center justify-between">
                <Label for="password">Password</Label>
                <Link
                  :href="route('password.request')"
                  class="text-sm font-medium text-primary hover:underline"
                >
                  Forgot password?
                </Link>
              </div>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                :disabled="isLoading"
                autocomplete="current-password"
                required
              />
              <InputError :message="form.errors.password" />
            </FormField>

            <div class="flex items-center space-x-2">
              <Checkbox
                id="remember"
                :checked="form.remember"
                @update:checked="value => form.remember = value"
                :disabled="isLoading"
              />
              <label
                for="remember"
                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" >
                Remember me
              </label>
            </div>
          </div>

          <Button type="submit" class="w-full" :disabled="isLoading">
            <IconLoader
              v-if="isLoading"
              class="mr-2 h-4 w-4 animate-spin"
            />
            Sign In
          </Button>
        </form>

        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t" />
          </div>
          <div class="relative flex justify-center text-xs uppercase">
            <span class="bg-background px-2 text-muted-foreground">
              Or continue with
            </span>
          </div>
        </div>

        <div class="grid gap-4">
          <Button variant="outline" type="button" :disabled="isLoading">
            <IconBrandGithub class="mr-2 h-4 w-4" />
            Github
          </Button>
          <Button variant="outline" type="button" :disabled="isLoading">
            <IconBrandGoogle class="mr-2 h-4 w-4" />
            Google
          </Button>
        </div>

        <p class="px-8 text-center text-sm text-muted-foreground">
          Don't have an account?
          <Link
            :href="route('register')"
            class="text-primary hover:underline">
            Sign up
          </Link>
        </p>
      </div>
    </div>
  </AuthLayout>
</template>
