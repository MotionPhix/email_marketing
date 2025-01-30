<script setup lang="ts">
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import {Head, Link, useForm} from '@inertiajs/vue3';
import {Checkbox} from "@/Components/ui/checkbox";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle
} from "@/Components/ui/card";

defineProps({
  canResetPassword: Boolean,
  status: String,
});

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
  <Head title="Login" />

  <AuthenticationCard>

    <form @submit.prevent="submit" class="w-full max-w-md">
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
                  <Checkbox v-model:checked="form.remember" name="remember"/>
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
      </Card>
    </form>
  </AuthenticationCard>
</template>
