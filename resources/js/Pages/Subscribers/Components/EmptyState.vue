<script setup lang="ts">
import { Users, Upload, FileDown } from 'lucide-vue-next'

defineProps<{
  onAdd: () => void
  onImport: () => void
  onTemplate: () => void
}>()
</script>

<template>
  <div class="flex min-h-[400px] flex-col items-center justify-center rounded-md border border-dashed p-8 text-center animate-in fade-in-50">
    <div class="mx-auto flex max-w-[420px] flex-col items-center justify-center text-center">
      <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-900">
        <Users class="h-10 w-10 text-primary-500" />
      </div>

      <h3 class="mt-4 text-lg font-semibold">No subscribers yet</h3>
      <p class="mt-2 text-sm text-muted-foreground">
        Get started by adding your first subscriber or import your existing list.
      </p>

      <div class="mt-6 flex flex-col gap-2 sm:flex-row">
        <Button @click="onAdd">
          Add subscriber
        </Button>

        <Button variant="outline" @click="onImport">
          <Upload class="mr-2 h-4 w-4" />
          Import list
        </Button>

        <Button variant="ghost" @click="onTemplate">
          <FileDown class="mr-2 h-4 w-4" />
          Get template
        </Button>
      </div>
    </div>
  </div>
</template>
