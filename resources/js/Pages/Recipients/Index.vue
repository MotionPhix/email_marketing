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
import {FilterXIcon} from "lucide-vue-next";
import { visitModal } from '@inertiaui/modal-vue'
import {debounce} from "maz-ui";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/Components/ui/tooltip'

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
        <Button
          v-if="recipients.data.length"
          class="bg-stone-500" size="icon"
          :href="route('recipients.create')" as-child>
          <GlobalLink as="button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M12.5 22H6.59087C5.04549 22 3.81631 21.248 2.71266 20.1966C0.453365 18.0441 4.1628 16.324 5.57757 15.4816C7.67837 14.2307 10.1368 13.7719 12.5 14.1052C13.3575 14.2261 14.1926 14.4514 15 14.7809" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M16.5 6.5C16.5 8.98528 14.4853 11 12 11C9.51472 11 7.5 8.98528 7.5 6.5C7.5 4.01472 9.51472 2 12 2C14.4853 2 16.5 4.01472 16.5 6.5Z" stroke="currentColor" stroke-width="1.5" />
              <path d="M18.5 22L18.5 15M15 18.5H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
          </GlobalLink>
        </Button>

        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger as-child>
              <Button size="icon" as-child :href="route('recipients.import')">
                <GlobalLink as="button">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                    <path d="M11.1002 3C7.45057 3.00657 5.53942 3.09617 4.31806 4.31754C3 5.63559 3 7.75698 3 11.9997C3 16.2425 3 18.3639 4.31806 19.6819C5.63611 21 7.7575 21 12.0003 21C16.243 21 18.3644 21 19.6825 19.6819C20.9038 18.4606 20.9934 16.5494 21 12.8998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M20.9995 6.02511L20 6.02258C16.2634 6.01313 14.3951 6.0084 13.0817 6.95247C12.6452 7.2662 12.2622 7.64826 11.9474 8.08394C11 9.39497 11 11.2633 11 14.9998M20.9995 6.02511C21.0062 5.86248 20.9481 5.69887 20.8251 5.55315C20.0599 4.64668 18.0711 2.99982 18.0711 2.99982M20.9995 6.02511C20.9934 6.17094 20.9352 6.31598 20.8249 6.44663C20.0596 7.35292 18.0711 8.99982 18.0711 8.99982" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </GlobalLink>
              </Button>
            </TooltipTrigger>

            <TooltipContent>
              <p>Import recipients</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <!-- Search Bar -->
        <SearchBar v-model="searchQuery" @search="fetchRecipients"/>
      </div>
    </template>

    <div class="py-12 px-6">
      <div class="flex gap-4" v-if="recipients.data.length">
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
              class="bg-lime-500">
              <FilterXIcon/>
            </Button>

            <span class="flex-1"/>

            <BatchActions
              @perform-action="handleAction"
              :selected-recipients="Array.from(selectedRecipients)"/>
          </div>

          <div class="border rounded-xl overflow-hidden">
            <RecipientTable
              :recipients="recipients.data"
              :selected-recipients="selectedRecipients"
              @toggle-recipient="toggleRecipient"
              @select-all="selectAllRecipients"
            />
          </div>

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

        <div v-else class="flex items-center justify-center h-full">
          <div class="text-center grid gap-1">

            <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none">
              <path d="M11 22L9.52157 19.6461C8.49181 18.0065 7.97692 17.1867 7.16053 17.0393C5.83152 16.7993 4.45794 17.7045 3.5 18.509" stroke="currentColor" stroke-width="1.5" />
              <path d="M3.5 9V16.0279C3.5 17.2307 3.5 17.8321 3.7987 18.3154C4.0974 18.7987 4.63531 19.0677 5.71115 19.6056L9.65542 21.5777C10.4962 21.9981 10.5043 22 11.4443 22H14.5C17.3284 22 18.7426 22 19.6213 21.1213C20.5 20.2426 20.5 18.8284 20.5 16V9C20.5 6.17157 20.5 4.75736 19.6213 3.87868C18.7426 3 17.3284 3 14.5 3H9.5C6.67157 3 5.25736 3 4.37868 3.87868C3.5 4.75736 3.5 6.17157 3.5 9Z" stroke="currentColor" stroke-width="1.5" />
              <path d="M12 9H8M16 14H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M17 2V4M12 2V4M7 2V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <h2 class="text-xl font-semibold text-gray-700">No Recipients Found</h2>

            <p class="text-gray-500 mb-2">
              You currently have no recipients.
            </p>

            <div>
              <Button
                as-child :href="route('recipients.create')">
                <GlobalLink as="button">
                  Add Recipient
                </GlobalLink>
              </Button>
            </div>

          </div>
        </div>

    </div>

  </AppLayout>
</template>
