<script setup lang="ts">
import {onMounted, onUnmounted, ref, toRaw, watch} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import SearchBar from "@/Components/Recipient/SearchBar.vue";
import FilterSidebar from "@/Components/Recipient/FilterSidebar.vue";
import BatchActions from "@/Components/Recipient/BatchActions.vue";
import RecipientTable from "@/Components/Recipient/RecipientTable.vue";
import Pagination from "@/Components/Recipient/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";
import {FileSymlinkIcon, FilterXIcon} from "lucide-vue-next";
import {debounce} from "maz-ui";

// Define the `recipients` prop
const {recipients} = defineProps<{
  recipients: {
    data: Array<{ id: number; name: string; email: string; status: string }>;
    first_page_url?: string;
    last_page_url?: string;
    next_page_url?: string;
    prev_page_url?: string;
    current_page: number;
    last_page: number;
    per_page: number;
    links: Array<{ url?: string, label?: string, active: boolean }>;
    total: number;
  };
}>();

const searchQuery = ref("");
const filters = ref({status: null});
const selectedRecipients = ref(new Set<number>());
const page = usePage();

const toggleRecipient = (id: number) => {
  selectedRecipients.value.has(id)
    ? selectedRecipients.value.delete(id)
    : selectedRecipients.value.add(id);
};

const selectAllRecipients = (selectAll: boolean) => {
  recipients.data.forEach(({id}) => {
    selectAll ? selectedRecipients.value.add(id) : selectedRecipients.value.delete(id);
  });
};

const fetchRecipients = debounce(async (paramsOrUrl: Record<string, any> | string) => {
  if (typeof paramsOrUrl === "string") {
    // If a URL is provided, visit it directly
    await router.get(paramsOrUrl, {}, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
    return;
  }

  const query = {};

  // Add search query if present
  if (searchQuery.value) {
    query.search = searchQuery.value;
  }

  // Add filters if any are present
  if (filters.value.status) {
    switch (filters.value.status) {
      case 'male':
      case 'female':
      case 'unspecified':
        query.gender = filters.value.status
        break;

      default:
        query.status = filters.value.status
        break;
    }
  }

  const finalParams = { ...query, ...paramsOrUrl };

  await router.get(route("recipients.index"), finalParams, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}, 300);

// Deselect all recipients
const deselectAllRecipients = () => {
  selectedRecipients.value.clear();
};

// Clear filters and reset the search
const clearFilters = () => {
  filters.value = {status: null}; // Reset filters to their default values
  searchQuery.value = ""; // Reset the search query

  // Adjust the URL to remove filter parameters and keep the search query if any
  const params = searchQuery.value ? {search: searchQuery.value} : {};

  fetchRecipients(params); // Fetch recipients with the cleared filters and updated URL
};

// Watch for changes in search and filters
watch([searchQuery, filters], ([newSearchQuery, newFilters]) => {
  fetchRecipients()

  localStorage.setItem(
    "filters",
    JSON.stringify(toRaw(newFilters))
  );
});

onMounted(() => {
  const savedFilters = localStorage.getItem("filters");

  if (savedFilters) {
    try {
      const parsedFilters = JSON.parse(savedFilters);
      filters.value = { status: null, ...parsedFilters }; // Merge defaults
    } catch (e) {
      console.error("Failed to parse saved filters", e);
    }
  }
})

onUnmounted(() => {
  localStorage.removeItem("filters");
});
</script>

<template>
  <AppLayout title="Explore Recipients">

    <!-- Header -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Explore Recipients
      </h2>
    </template>

    <!-- Action Button -->
    <template #action>
      <div class="flex items-center gap-2">
        <Button size="icon" as-child :href="route('recipients.import')">
          <GlobalLink as="button">
            <FileSymlinkIcon />
          </GlobalLink>
        </Button>

        <!-- Search Bar -->
        <SearchBar v-model="searchQuery" @search="fetchRecipients"/>
      </div>
    </template>

    <div class="py-12 px-6">
      <div class="flex gap-4">
        <!-- Filter Sidebar -->
        <FilterSidebar v-model="filters"/>

        <!-- Recipient Table -->
        <div class="flex-1 grid gap-6">
          <!-- Deselect All and Clear Filters Buttons -->
          <div class="mt-4 flex items-center gap-2 pt-5">
            <Button
              @click="deselectAllRecipients"
              class="bg-gray-300 p-2 rounded hover:bg-gray-400 transition duration-200"
            >
              Deselect All
            </Button>

            <Button
              size="icon"
              @click="clearFilters"
              class="bg-red-500">
              <FilterXIcon />
            </Button>

            <BatchActions
              :selected-recipients="Array.from(selectedRecipients)"/>
          </div>

          <RecipientTable
            :recipients="recipients.data"
            :selected-recipients="selectedRecipients"
            @toggle-recipient="toggleRecipient"
            @select-all="selectAllRecipients"
          />

          <Pagination
            :links="recipients.links"
            :per-page="recipients.per_page"
            :current-page="recipients.current_page"
            :total="recipients.total"
            :first-page-url="recipients.first_page_url"
            :last-page-url="recipients.last_page_url"
            :next-page-url="recipients.next_page_url"
            :prev-page-url="recipients.prev_page_url"
            :last-page="recipients.last_page"
            @change-page="fetchRecipients" />

        </div>
      </div>
    </div>

  </AppLayout>
</template>
