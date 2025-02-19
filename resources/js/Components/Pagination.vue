<script setup lang="ts">
interface Link {
  url: string | null
  label: string
  active: boolean
}

interface PaginationData {
  current_page: number
  data: any[]
  first_page_url: string
  from: number | null
  last_page: number
  last_page_url: string
  links: Link[]
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number | null
  total: number
}

interface Props {
  pagination: PaginationData
}

const props = defineProps<Props>()

const hasPages = computed(() => props.pagination.total > props.pagination.per_page)

// Strip HTML entities from labels
const cleanLabel = (label: string) => {
  return label
    .replace(/&laquo;/g, '«')
    .replace(/&raquo;/g, '»')
}

// Get numbered pages excluding Previous and Next links
const numberedLinks = computed(() => {
  return props.pagination.links.filter((link, index) => {
    return index !== 0 && index !== props.pagination.links.length - 1
  })
})
</script>

<template>
  <div v-if="hasPages" class="flex items-center justify-between px-2 py-3 sm:px-6">
    <div class="flex flex-1 items-center justify-between sm:hidden">
      <!-- Mobile pagination -->
      <Button
        variant="outline"
        size="sm"
        :disabled="!pagination.prev_page_url"
        @click="pagination.prev_page_url && $inertia.get(pagination.prev_page_url)"
      >
        Previous
      </Button>
      <div class="text-sm text-muted-foreground">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </div>
      <Button
        variant="outline"
        size="sm"
        :disabled="!pagination.next_page_url"
        @click="pagination.next_page_url && $inertia.get(pagination.next_page_url)"
      >
        Next
      </Button>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <!-- Results count -->
      <div v-if="pagination.total" class="text-sm text-muted-foreground">
        Showing
        <span class="font-medium">{{ pagination.from }}</span>
        to
        <span class="font-medium">{{ pagination.to }}</span>
        of
        <span class="font-medium">{{ pagination.total }}</span>
        results
      </div>
      <div v-else class="text-sm text-muted-foreground">
        No results found
      </div>

      <!-- Desktop pagination -->
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <!-- Previous page -->
          <Button
            variant="outline"
            size="icon"
            class="rounded-l-md"
            :disabled="!pagination.prev_page_url"
            @click="pagination.prev_page_url && $inertia.get(pagination.prev_page_url)"
          >
            <span class="sr-only">Previous page</span>
            <IconChevronLeft class="h-4 w-4" />
          </Button>

          <!-- Numbered pages -->
          <Button
            v-for="link in numberedLinks"
            :key="link.label"
            variant="outline"
            size="icon"
            :class="[
              link.active && 'bg-primary text-primary-foreground hover:bg-primary/90',
              'rounded-none'
            ]"
            :disabled="!link.url"
            @click="link.url && $inertia.get(link.url)"
          >
            {{ cleanLabel(link.label) }}
          </Button>

          <!-- Next page -->
          <Button
            variant="outline"
            size="icon"
            class="rounded-r-md"
            :disabled="!pagination.next_page_url"
            @click="pagination.next_page_url && $inertia.get(pagination.next_page_url)"
          >
            <span class="sr-only">Next page</span>
            <IconChevronRight class="h-4 w-4" />
          </Button>
        </nav>
      </div>
    </div>
  </div>
</template>
