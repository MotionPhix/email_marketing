<script setup>
import ApplicationMark from "@/Components/ApplicationMark.vue";
</script>

<template>
  <div class="flex gap-x-2 items-center">
    <ApplicationMark class="w-8 h-8" />
    <span>{{ $page.props.app.name }}</span>
  </div>
</template>
