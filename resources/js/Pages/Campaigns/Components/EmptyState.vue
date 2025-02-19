<script setup lang="ts">
import { IconMail, IconPlus } from '@tabler/icons-vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center animate-in fade-in-50">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-muted">
      <IconMail class="h-6 w-6 text-foreground" />
    </div>

    <h3 class="mt-4 text-lg font-semibold">No campaigns yet</h3>

    <p class="mt-2 text-sm text-muted-foreground">
      Create your first email campaign to start connecting with your audience.
    </p>

    <div class="mt-6">
      <Button asChild>
        <Link :href="route('campaigns.create')">
          <IconPlus class="mr-2 h-4 w-4" />
          Create Campaign
        </Link>
      </Button>
    </div>
  </div>
</template>
