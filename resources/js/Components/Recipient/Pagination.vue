<script setup lang="ts">
import {Pagination} from "@/Components/ui/pagination";
import {PaginationList, PaginationListItem} from "radix-vue";

const {current, totalPages, perPage, links} = defineProps<{
  current: number;
  totalPages: number;
  perPage: number;
  links: [];
}>();

const emit = defineEmits(["change-page"]);
</script>

<template>
  <Pagination
    show-edges
    :page="current"
    :total="totalPages"
    :sibling-count="1"
    :default-page="2"
    @change="page => emit('change-page', page)"
  />

  <Pagination
    :items-per-page="perPage"
    v-slot="{ page }" :total="totalPages"
    @change="page => emit('change-page', page)"
    :sibling-count="1" show-edges
    :default-page="current">
    <PaginationList v-slot="{ items }" class="flex items-center gap-1">
      <PaginationFirst class="size-7"/>
      <PaginationPrev class="size-7"/>

      <template v-for="(item, index) in items">

        <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
          <Button class="size-7 p-0" :variant="item.value === page ? 'default' : 'outline'">
            {{ item.value }}
          </Button>
        </PaginationListItem>

        <PaginationEllipsis v-else :key="item.type" :index="index"/>

      </template>

      <PaginationNext class="size-7"/>
      <PaginationLast class="size-7"/>

    </PaginationList>

  </Pagination>
</template>
