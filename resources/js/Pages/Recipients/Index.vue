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
import { visitModal } from '@inertiaui/modal-vue'
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

  const finalParams = {...query, ...paramsOrUrl};

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

const handleAction = (payload: { action: string; recipients: number[] }) => {
  const {action, recipients} = payload;
  switch (action) {
    case "delete":
      // Handle deletion logic
      router.get(route('recipients.batch', {
        action: action,
        recipients: recipients
      }), {}, {
        preserveScroll: true,
        onSuccess: () => {
          console.log('deleted recipients')
        },
        onError: (errors) => {
          console.log(errors)
        }
      });
      break;
    case "export_pdf":
      // Handle export to PDF
      window.open(route('recipients.batch', {
        action: action,
        recipients: recipients,
      }), '_blank')
      break;
    case "export_excel":
      // Handle export to Excel
      window.open(route('recipients.batch', {
        action: action,
        recipients: recipients,
      }), '_blank')
      break;
    case "export_csv":
      // Handle export to CSV
      window.open(route('recipients.batch', {
        action: action,
        recipients: recipients,
      }), '_blank')
      break;
    default:
      // Edit recipient
      visitModal(route('recipients.batch', { action: action, recipients: recipients }), {
        method: 'get',
        data: {},
        onClose: () => {
          console.log('Recipient edited')
          deselectAllRecipients()
        },
      });
  }
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
      filters.value = {status: null, ...parsedFilters}; // Merge defaults
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
            <FileSymlinkIcon/>
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
              size="icon"
              :disabled="! selectedRecipients.size"
              @click="deselectAllRecipients"
              class="bg-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000"
                   fill="none">
                <path
                  d="M5.08069 15.2964C3.86241 16.0335 0.668175 17.5386 2.61368 19.422C3.56404 20.342 4.62251 21 5.95325 21H13.5468C14.8775 21 15.936 20.342 16.8863 19.422C18.8318 17.5386 15.6376 16.0335 14.4193 15.2964C11.5625 13.5679 7.93752 13.5679 5.08069 15.2964Z"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path
                  d="M13.5 7C13.5 9.20914 11.7091 11 9.5 11C7.29086 11 5.5 9.20914 5.5 7C5.5 4.79086 7.29086 3 9.5 3C11.7091 3 13.5 4.79086 13.5 7Z"
                  stroke="currentColor" stroke-width="1.5"/>
                <path d="M17 5L22 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path d="M17 8L22 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path d="M20 11L22 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
              </svg>
            </Button>

            <Button
              size="icon"
              :disabled="! filters.status"
              @click="clearFilters"
              class="bg-red-500">
              <FilterXIcon/>
            </Button>

            <span class="flex-1"/>

            <BatchActions
              @perform-action="handleAction"
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
            @change-page="fetchRecipients"/>

        </div>
      </div>
    </div>

  </AppLayout>
</template>
