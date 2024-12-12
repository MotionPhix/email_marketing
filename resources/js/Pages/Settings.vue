<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import PageTitle from "@/Components/PageTitle.vue";

defineProps<{
  settings: object
}>();
</script>

<template>
  <AppLayout title="Settings">
    <template #header>
      <PageTitle title="Settings" />
    </template>

    <div class="py-12">
      <pre>
        settings
      </pre>
    </div>
  </AppLayout>
</template>
