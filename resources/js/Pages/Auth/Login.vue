<<<<<<< HEAD
<script setup lang="ts">
import {useForm, Link} from "@inertiajs/vue3";
import {Label} from "@/Components/ui/label";
import {Input} from "@/Components/ui/input"
import {Button} from "@/Components/ui/button";
import {Checkbox} from "@/Components/ui/checkbox";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle
} from "@/Components/ui/card";
import InputError from "@/Components/InputError.vue";
=======
<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import {Checkbox} from '@/Components/ui/checkbox';

defineProps({
  canResetPassword: Boolean,
  status: String,
});
>>>>>>> 7982e179b2ab1aed9615c42d2150bfe9decd54fe

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login');
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">

    <form @submit.prevent="submit" class="w-full max-w-md">
      <Card>

        <CardHeader>
          <CardTitle>
            Login
          </CardTitle>
        </CardHeader>

<<<<<<< HEAD
        <CardContent class="grid gap-4">
          <div>
            <Label for="email" class="block text-sm font-medium text-gray-700">Email</Label>
            <Input
              type="email"
              id="email"
              placeholder="Enter your email"
              v-model="form.email"
              class="mt-1"
            />

            <InputError :message="form.errors.email" />
          </div>

          <div>
            <Label for="password" class="block text-sm font-medium text-gray-700">Password</Label>
            <Input
              type="password"
              id="password"
              placeholder="Enter your password"
              v-model="form.password"
              class="mt-1"
            />

            <InputError :message="form.errors.password" />
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <Checkbox
                id="remember"
                :checked="form.remember"
                @update:checked="form.remember != form.remember"
              />
              <Label
                for="remember"
                class="ml-2 block text-sm text-gray-900">
                Remember me
              </Label>
            </div>

            <div class="text-sm">
              <Link
                href="/forgot-password"
                class="font-medium text-blue-600 hover:text-blue-500">
                Forgot your password?
              </Link>
            </div>
          </div>
        </CardContent>

        <CardFooter>
          <Button
            type="submit">
            Login
          </Button>
        </CardFooter>

=======
    <form @submit.prevent="submit">

      <Card class="mx-auto max-w-md">
        <CardHeader>
          <CardTitle class="text-2xl">
            Login
          </CardTitle>

          <CardDescription>
            Enter your email below to login to your account
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="grid gap-4">
            <div class="grid gap-2">
              <FormField
                label="Email"
                type="email"
                v-model="form.email"
                :error="form.errors.email"
                placeholder="m@example.com"
                autofocus
                required
              />
            </div>

            <div class="grid gap-2">
              <div class="flex items-center" v-if="canResetPassword">
                <Label for="password">Password</Label>
                <Link
                  as="button"
                  :href="route('password.request')"
                  class="ml-auto inline-block text-sm underline">
                  Forgot your password?
                </Link>
              </div>

              <div>
                <FormField
                  v-model="form.password"
                  placeholder="Enter your password"
                  :error="form.errors.password"
                  type="password"
                  required
                />
              </div>

              <div class="block mt-1">
                <label class="flex items-center">
                  <Checkbox v-model:checked="form.remember" name="remember" />
                  <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
              </div>

            </div>

            <Button type="submit" class="w-full">
              Login
            </Button>
          </div>

          <div class="mt-4 text-center text-sm">
            Don't have an account?
            <Link as="button" :href="route('register')" class="underline">
              Sign up
            </Link>
          </div>
        </CardContent>
>>>>>>> 7982e179b2ab1aed9615c42d2150bfe9decd54fe
      </Card>
    </form>
  </div class="flex items-center justify-center min-h-screen bg-gray-100">
</template>
