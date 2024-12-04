<script setup lang="ts">
import {Pagination} from "@/Components/ui/pagination";

const {current, totalPages} = defineProps<{
  current: number;
  totalPages: number;
}>();

const emit = defineEmits(["change-page"]);
</script>

<template>
  <Pagination
    :page="current"
    :total="totalPages"
    @change="page => emit('change-page', page)"
  />
</template>
