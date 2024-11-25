<script setup>
import {Head, Link} from '@inertiajs/vue3'
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";

defineProps({templates: Array});
</script>

<template>
  <Head>
    <title>Templates</title>
  </Head>

  <AppLayout title="Templates">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Explore templates
      </h2>
    </template>

    <div class="py-12">
      <div class="mt-4">
        <Button as-child>
          <Link :href="route('templates.create')" as="button">
            Create Template
          </Link>
        </Button>

        <div v-for="template in templates" :key="template.id" class="border rounded p-4 mt-4">
          <h2 class="text-lg font-semibold">{{ template.name }}</h2>
          <p class="truncate">{{ template.content }}</p>
          <div class="mt-2 flex gap-2">
            <Link :href="`/templates/${template.id}/edit`" class="btn btn-sm btn-secondary">Edit</Link>
            <button class="btn btn-sm btn-danger" @click="deleteTemplate(template.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
