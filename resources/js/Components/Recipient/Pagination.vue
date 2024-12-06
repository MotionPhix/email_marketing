<script setup lang="ts">
import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from "@/Components/ui/pagination";
import {Button} from "@/Components/ui/button";
import {computed} from "vue";

const {
  links,
} = defineProps<{
  firstPageUrl?: string;
  lastPageUrl?: string;
  nextPageUrl?: string;
  prevPageUrl?: string;
  currentPage: number;
  lastPage: number;
  perPage: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}>();

// Emit event for changing the page
const emit = defineEmits(["change-page"]);

const computedLinks = computed(() => {
  const result = [];
  const threshold = 2; // Number of pages to show before inserting ellipsis
  const pageLinks = links.filter((link) => !isNaN(Number(link.label)));

  pageLinks.forEach((link, index) => {
    // Add the first page and always include it
    if (index === 0 || index === pageLinks.length - 1) {
      result.push(link);
    } else {
      const prevPage = Number(pageLinks[index - 1]?.label);
      const currPage = Number(link.label);

      // Add ellipsis if there's a gap larger than the threshold
      if (currPage - prevPage > threshold) {
        result.push({ label: "...", url: null });
      }

      result.push(link);
    }
  });

  // Include "Previous" and "Next" links
  result.unshift(links.find((link) => link.label.includes("Previous")));
  result.push(links.find((link) => link.label.includes("Next")));

  return result.filter(Boolean);
});
</script>

<template>
  <Pagination
    :page="currentPage"
    :total="total"
    :items-per-page="perPage"
    :default-page="currentPage"
    :sibling-count="1"
    show-edges>
    <PaginationList v-slot="{ items }" class="flex items-center gap-1">
      <!-- First Page -->
      <PaginationFirst
        class="size-7"
        :disabled="! prevPageUrl"
        @click="emit('change-page', firstPageUrl)"
      />

      <!-- Previous Page -->
      <PaginationPrev
        class="size-7"
        :disabled="!prevPageUrl"
        @click="emit('change-page', prevPageUrl)"
      />

      <!-- Pagination Items -->
      <template v-for="(link, index) in (computedLinks)" :key="index">
        <!-- Render Page Buttons -->
        <PaginationListItem
          v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')"
          :value="link.url" as-child>
          <Button
            class="size-7 p-0"
            :variant="link.active ? 'default' : 'outline'"
            @click="emit('change-page', link.url)">
            {{ link.label }}
          </Button>
        </PaginationListItem>

        <!-- Ellipsis -->
        <PaginationEllipsis v-else-if="link.label === '...'" :key="link.label" />
      </template>

      <!-- Next Page -->
      <PaginationNext
        class="size-7"
        :disabled="! nextPageUrl"
        @click="emit('change-page', nextPageUrl)"  />

      <!-- Last Page -->
      <PaginationLast
        class="size-7"
        :disabled="! nextPageUrl"
        @click="emit('change-page', lastPageUrl)"/>
    </PaginationList>
  </Pagination>
</template>
