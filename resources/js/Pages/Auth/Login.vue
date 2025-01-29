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

      </Card>
    </form>
  </div class="flex items-center justify-center min-h-screen bg-gray-100">
</template>
