<script setup lang="ts">
import { SearchIcon } from "lucide-vue-next"

const model = defineModel();
</script>

<template>
  <div class="relative flex items-center w-full">
    <Input
      v-model="model"
      placeholder="Search recipients"
    />

    <div class="absolute right-1 flex items-center gap-1">
      <Button
        disabled
        type="button"
        variant="ghost"
        size="icon"
        class="h-7 w-7">
        <SearchIcon class="h-4 w-4" />
      </Button>
    </div>
  </div>
</template>
