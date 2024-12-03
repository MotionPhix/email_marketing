<script setup>
import {Link} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";

defineProps({templates: Array});
</script>

<template>
  <AppLayout title="Templates">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Explore templates
      </h2>
    </template>

    <template #action>
      <Button as-child :href="route('templates.create')">
        <Link as="button">
          New
        </Link>
      </Button>
    </template>

    <div class="py-12">
      <div class="grid grid-cols-3 gap-6">
        <div
          v-for="template in templates"
          :key="template.id" class="border rounded-lg">

          <h2 class="p-2 text-lg font-semibold sticky top-0 z-10 bg-gray-100">{{ template.name }}</h2>

          <section class="h-72 overflow-y-auto px-2" v-html="template.content" />

          <div class="mt-2 flex gap-2 p-2 justify-end">
            <Button
              as-child
              :href="route('templates.edit', template.uuid)" >
              <Link
                as="button">
                Edit
              </Link>
            </Button>

            <Button
              as-child
              method="delete"
              preserve-scroll
              :href="route('templates.destroy',  template.uuid)"
              variant="destructive">
              <Link as="button">
                Delete
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
