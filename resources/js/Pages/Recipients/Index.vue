<script setup lang="ts">
import {onMounted, onUnmounted, ref, toRaw, watch} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import SearchBar from "@/Components/Recipient/SearchBar.vue";
import FilterSidebar from "@/Components/Recipient/FilterSidebar.vue";
import BatchActions from "@/Components/Recipient/BatchActions.vue";
import RecipientTable from "@/Components/Recipient/RecipientTable.vue";
import Pagination from "@/Components/Recipient/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {FilterXIcon, UserXIcon} from "lucide-vue-next";
import { visitModal } from '@inertiaui/modal-vue'
import {debounce} from "maz-ui";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/Components/ui/tooltip'
import PageTitle from "@/Components/PageTitle.vue";
import EmptyState from "@/Components/EmptyState.vue";

const props = defineProps<{
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
  props.recipients.data.forEach(({id}) => {
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
      console.log('nothing to do')
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
      <PageTitle title="Explore Recipients" />
    </template>

    <!-- Action Button -->
    <template #action>
      <div class="flex items-center gap-2">
        <Button
          v-if="recipients.data.length"
          class="bg-stone-500" size="icon"
          :href="route('recipients.create')" as-child>
          <GlobalLink as="button">
            <svg class="shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
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
                  <svg class="shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                    <path d="M20 15.0057V10.6606C20 9.84276 20 9.43383 19.8478 9.06613C19.6955 8.69843 19.4065 8.40927 18.8284 7.83096L14.0919 3.09236C13.593 2.59325 13.3436 2.3437 13.0345 2.19583C12.9702 2.16508 12.9044 2.13778 12.8372 2.11406C12.5141 2 12.1614 2 11.4558 2C8.21082 2 6.58831 2 5.48933 2.88646C5.26731 3.06554 5.06508 3.26787 4.88607 3.48998C4 4.58943 4 6.21265 4 9.45908V14.0052C4 17.7781 4 19.6645 5.17157 20.8366C6.11466 21.7801 7.52043 21.9641 10 22M13 2.50022V3.00043C13 5.83009 13 7.24492 13.8787 8.12398C14.7574 9.00304 16.1716 9.00304 19 9.00304H19.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M15 22C14.3932 21.4102 12 19.8403 12 19C12 18.1597 14.3932 16.5898 15 16M13 19H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
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
        <SearchBar
          v-model="searchQuery"
          @search="fetchRecipients"
          class="hidden sm:inline-flex"
        />
      </div>
    </template>

    <div class="py-0 sm:py-12 px-6">
      <div class="flex gap-4 flex-col sm:flex-row" v-if="recipients.data.length">
        <!-- Filter Sidebar -->
        <div class="flex gap-1 sm:block items-center mt-6 sm:mt-0">

          <div class="flex-1 sm:hidden">

            <SearchBar
              v-model="searchQuery"
              @search="fetchRecipients"
            />

          </div>

          <FilterSidebar v-model="filters"/>
        </div>

        <!-- Recipient Table -->
        <div class="flex-1 grid gap-6">
          <!-- Deselect All and Clear Filters Buttons -->
          <div class="flex items-center gap-2 pt-5">
            <Button
              size="icon"
              :disabled="! selectedRecipients.size"
              @click="deselectAllRecipients"
              class="bg-gray-300">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24" width="24" height="24"
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

          <div class="border bg-gray-100 rounded-xl overflow-hidden">
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
          <EmptyState
            :icon="UserXIcon"
            title="No Recipients Found"
            description="You currently have no recipients.">
            <template #action>
              <Button
                as-child :href="route('recipients.create')">
                <GlobalLink as="button">
                  Add Recipient
                </GlobalLink>
              </Button>
            </template>
          </EmptyState>
        </div>

    </div>

  </AppLayout>
</template>
