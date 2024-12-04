<script setup lang="ts">
import {ref, watch} from "vue";
import {router} from "@inertiajs/vue3";
import SearchBar from "@/Components/Recipient/SearchBar.vue";
import FilterSidebar from "@/Components/Recipient/FilterSidebar.vue";
import BatchActions from "@/Components/Recipient/BatchActions.vue";
import RecipientTable from "@/Components/Recipient/RecipientTable.vue";
import Pagination from "@/Components/Recipient/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";

// Define the `recipients` prop
const {recipients} = defineProps<{
  recipients: {
    data: Array<{ id: number; name: string; email: string; status: string }>;
    current_page: number;
    last_page: number;
    total: number;
  };
}>();

const searchQuery = ref("");
const filters = ref({status: null});
const selectedRecipients = ref(new Set<number>());

const fetchRecipients = async (params = {}) => {
  // Construct the query object dynamically
  const query = {};

  // Add search query if present
  if (searchQuery.value) {
    query.search = searchQuery.value;
  }

  // Add filters if any are present
  if (filters.value.status) {
    query.status = filters.value.status;
  }

  // Merge the additional params passed into the function
  const finalParams = { ...query, ...params };

  await router.get(
    route("recipients.index"),
    finalParams,
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

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

// Deselect all recipients
const deselectAllRecipients = () => {
  selectedRecipients.value.clear();
};

// Clear filters and reset the search
const clearFilters = () => {
  filters.value = { status: null }; // Reset filters to their default values
  searchQuery.value = ""; // Reset the search query

  // Adjust the URL to remove filter parameters and keep the search query if any
  const params = searchQuery.value ? { search: searchQuery.value } : {};

  fetchRecipients(params); // Fetch recipients with the cleared filters and updated URL
};

// Watch for changes in search and filters
watch([searchQuery, filters], () => fetchRecipients());
</script>

<template>
  <AppLayout>

    <!-- Header -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Recipients
      </h2>
    </template>

    <!-- Action Button -->
    <template #action>
      <!-- Search Bar -->
      <SearchBar v-model="searchQuery" @search="fetchRecipients"/>
    </template>

    <div class="py-12 px-6">
      <div class="flex gap-4">
        <!-- Filter Sidebar -->
        <FilterSidebar v-model="filters"/>

        <!-- Recipient Table -->
        <div class="flex-1">
          <!-- Deselect All and Clear Filters Buttons -->
          <div class="mt-4 flex items-center gap-2 pt-5">
            <Button
              @click="deselectAllRecipients"
              class="bg-gray-300 p-2 rounded hover:bg-gray-400 transition duration-200"
            >
              Deselect All
            </Button>

            <Button
              @click="clearFilters"
              class="bg-red-500 text-white p-2 rounded hover:bg-red-600 transition duration-200"
            >
              Clear Filters
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
            :current="recipients.current_page"
            :total-pages="recipients.last_page"
            @change-page="page => fetchRecipients({ page })"
          />
        </div>
      </div>
    </div>

  </AppLayout>
</template>
