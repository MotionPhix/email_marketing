<script setup lang="ts">
import {Link} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";
import PageTitle from "@/Components/PageTitle.vue";
import {FileScanIcon, PencilIcon, TrashIcon} from "lucide-vue-next";
import EmptyState from "@/Components/EmptyState.vue";

defineProps<{
  templates: Array<{
    id: number
    uuid: string
    name: string
    content: string
  }>
}>();
</script>

<template>
  <AppLayout title="Templates">

    <template #header>
      <PageTitle title="Explore Templates" />
    </template>

    <template #action v-if="templates.length">
      <Button as-child :href="route('templates.create')">
        <Link as="button">
          New
        </Link>
      </Button>
    </template>

    <div class="py-12">
      <div class="grid gap-4" v-if="!templates.length">
        <EmptyState
          :icon="FileScanIcon"
          title="No templates added yet"
          description="You don't have templates. The templates you create will appear here">
          <template #action>

            <Button as-child>
              <Link as="button" :href="route('templates.create')">
                Create your first template
              </Link>
            </Button>

          </template>
        </EmptyState>
      </div>

      <div class="px-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-6" v-else>
        <div
          v-for="template in templates"
          :key="template.id" class="border dark:bg-gray-700 rounded-lg overflow-hidden">

          <h2 class="p-2 text-lg dark:bg-gray-700 dark:border-b dark:border-gray-500 font-semibold sticky top-0 z-10 dark:text-gray-100 bg-gray-100">
            {{ template.name }}
          </h2>

          <section
            class="h-[29rem] scroll-smooth scrollbar-thin dark:text-gray-100 sm:h-72 overflow-y-auto px-2"
            v-html="template.content"/>

          <div class="mt-2 flex gap-2 p-2 justify-end">
            <Button
              as-child
              :href="route('templates.preview', template.id)">
              <GlobalLink
                as="button">
                Preview
              </GlobalLink>
            </Button>

            <Button
              as-child
              size="icon"
              variant="outline"
              :href="route('templates.edit', template.uuid)">
              <Link
                as="button">
                <PencilIcon class="dark:text-gray-100 h-5 w-5" />
                <span class="sr-only">Edit</span>
              </Link>
            </Button>

            <Button
              as-child
              size="icon"
              method="delete"
              preserve-scroll
              class="dark:text-gray-100"
              :href="route('templates.destroy',  template.uuid)"
              variant="ghost">
              <Link as="button">
                <TrashIcon class="dark:text-gray-100 h-5 w-5" />
                <span class="sr-only">Delete</span>
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
