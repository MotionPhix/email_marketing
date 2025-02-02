<script setup lang="ts">
import {onMounted, onUnmounted, ref, toRaw, watch, computed} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import SearchBar from "@/Components/Recipient/SearchBar.vue";
import BatchActions from "@/Components/Recipient/BatchActions.vue";
import RecipientTable from "@/Components/Recipient/RecipientTable.vue";
import Pagination from "@/Components/Recipient/Pagination.vue";
import PageTitle from "@/Components/PageTitle.vue";
import EmptyState from "@/Components/EmptyState.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {IconUsersMinus} from "@tabler/icons-vue";
import StatCard from "@/Pages/Recipients/Components/StatCard.vue";
import {useDeviceDetection} from "@/composables/useDeviceDetection";
import FilterModel from "@/Components/FilterModel.vue";
import {debounce} from "maz-ui";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/Components/ui/tooltip'
import {
  FilterXIcon,
  UserX,
  UserXIcon,
  UserPlusIcon,
  ImportIcon,
  Users,
  UserCheck,
  UserPlus
} from "lucide-vue-next";

// Enhanced Props Interface
interface Recipient {
  id: number;
  name: string;
  email: string;
  current_status: string;
  created_at: string;
  last_activity?: string;
  tags?: string[];
}

interface RecipientsPagination {
  data: Recipient[];
  first_page_url?: string;
  last_page_url?: string;
  next_page_url?: string;
  prev_page_url?: string;
  current_page: number;
  last_page: number;
  per_page: number;
  links: Array<{ url?: string; label?: string; active: boolean }>;
  total: number;
}

const props = defineProps<{
  recipients: RecipientsPagination;
  stats?: {
    total: number;
    active: number;
    unsubscribed: number;
    recent: number;
  };
  availableTags?: string[];
}>();

// Enhanced State Management
const searchQuery = ref("");
const isGlobalSelection = ref(false);
const {isMobile} = useDeviceDetection()
const page = usePage();

const selectedRecipients = ref(new Set<number>());
const sortConfig = ref({
  field: 'name',
  direction: 'asc'
});

const filters = ref({
  status: [] as string[],
  gender: [] as string[],
  dateRange: null,
  tags: [] as string[],
  activity: null as string | null
});

// Computed Properties
const hasActiveFilters = computed(() => {
  return filters.value.status.length > 0 ||
    filters.value.gender.length > 0 ||
    filters.value.dateRange !== null ||
    filters.value.tags.length > 0 ||
    filters.value.activity !== null;
});

// Enhanced Methods
const toggleSort = (field: string) => {
  if (sortConfig.value.field === field) {
    sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sortConfig.value.field = field;
    sortConfig.value.direction = 'asc';
  }

  fetchRecipients();
};

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

// Enhanced fetchRecipients with sorting and advanced filtering
const fetchRecipients = debounce(async (paramsOrUrl: Record<string, any> | string) => {
  if (typeof paramsOrUrl === "string") {
    await router.visit(paramsOrUrl, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
    return;
  }

  const query: Record<string, any> = {
    sort_by: sortConfig.value.field,
    sort_direction: sortConfig.value.direction
  };

  if (searchQuery.value) {
    query.search = searchQuery.value;
  }

  if (filters.value.status.length) {
    query.current_status = filters.value.status;
  }

  if (filters.value.gender.length) {
    query.gender = filters.value.gender;
  }

  if (filters.value.dateRange) {
    query.date_range = filters.value.dateRange;
  }

  if (filters.value.tags.length) {
    query.tags = filters.value.tags;
  }

  if (filters.value.activity) {
    query.activity = filters.value.activity;
  }

  const finalParams = {...query, ...paramsOrUrl};

  await router.get(route("recipients.index"), finalParams, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}, 300);

// Enhanced clearFilters
const clearFilters = () => {
  filters.value = {
    status: [],
    gender: [],
    dateRange: null,
    tags: [],
    activity: null
  };
  searchQuery.value = "";
  sortConfig.value = {field: 'name', direction: 'asc'};
  fetchRecipients();
};

const selectAllRecipientsAcrossPages = async () => {
  isGlobalSelection.value = true;
  try {
    const response = await window.axios.get(route('recipients.index'), {
      params: {
        all_recipients: true,
        fields: 'id'
      }
    });
    const allRecipients = response.data.data || response.data;
    allRecipients.forEach(({ id }) => {
      selectedRecipients.value.add(id);
    });
  } catch (error) {
    console.error("Error fetching all recipients:", error);
    isGlobalSelection.value = false;
  }
};

const selectionStatus = computed(() => {
  const totalRecipients = props.recipients.data.length;
  const selectedCount = selectedRecipients.value.size;

  return {
    count: selectedCount,
    isPartial: selectedCount > 0 && selectedCount < totalRecipients,
    isAll: selectedCount === totalRecipients,
    text: selectedCount > 0 ?
      `${selectedCount} recipient${selectedCount === 1 ? '' : 's'} selected` :
      ''
  };
});

// Function to deselect all recipients
const deselectAllRecipients = () => {
  selectedRecipients.value.clear()
}

const clearGlobalSelection = () => {
  isGlobalSelection.value = false;
  selectedRecipients.value.clear();
};

// Enhanced state persistence
watch([searchQuery, filters, sortConfig], ([newSearchQuery, newFilters, newSortConfig]) => {
  fetchRecipients();
  localStorage.setItem(
    "recipientFilters",
    JSON.stringify({
      filters: toRaw(newFilters),
      sort: toRaw(newSortConfig)
    })
  );
});

onMounted(() => {
  const saved = localStorage.getItem("recipientFilters");

  if (saved) {
    try {
      const {filters: savedFilters, sort: savedSort} = JSON.parse(saved);
      filters.value = {...filters.value, ...savedFilters};
      sortConfig.value = savedSort;
    } catch (e) {
      console.error("Failed to parse saved filters", e);
    }
  }
});

onUnmounted(() => {
  localStorage.removeItem("recipientFilters");
});
</script>

<template>
  <AppLayout title="Explore Recipients">
    <template #header>
      <PageTitle title="Explore Recipients"/>
    </template>

    <template #action>
      <div class="flex items-center gap-2">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger>
              <div>
                <GlobalLink
                  as="Button"
                  v-if="recipients.data.length"
                  variant="outline" size="icon"
                  :href="route('recipients.create')">
                  <UserPlusIcon/>
                </GlobalLink>
              </div>
            </TooltipTrigger>

            <TooltipContent>
              <p>Add New Recipient</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger>
              <div>
                <GlobalLink
                  variant="outline"
                  as="Button" size="icon"
                  :href="route('recipients.import')">
                  <ImportIcon class="h-4 w-4"/>
                </GlobalLink>
              </div>
            </TooltipTrigger>

            <TooltipContent>
              <p>Import Recipients</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <SearchBar
          v-model="searchQuery"
          @search="fetchRecipients"
          class="hidden sm:inline-flex"
        />
      </div>
    </template>

    <div class="py-12 px-6 mb-12">
      <!-- Stats Section -->
      <div
        v-if="stats && recipients.data.length"
        class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <StatCard
          title="Total Recipients"
          :value="stats.total"
          :icon="Users"
        />

        <StatCard
          title="Active Recipients"
          :value="stats.active"
          :icon="UserCheck"
          class="text-green-600"
        />

        <StatCard
          title="Unsubscribed"
          :value="stats.unsubscribed"
          :icon="UserX"
          class="text-red-600"
        />

        <StatCard
          title="New (Last 30 Days)"
          :value="stats.recent"
          :icon="UserPlus"
          class="text-blue-600"
        />
      </div>

      <div class="flex gap-4 flex-col sm:flex-row" v-if="recipients.data.length">
        <div class="flex gap-1 sm:block items-center mt-6 sm:mt-0">
          <div class="flex-1 sm:hidden">
            <SearchBar
              v-model="searchQuery"
              @search="fetchRecipients"
            />
          </div>

          <FilterModel
            v-model="filters"
            :available-tags="availableTags"
            @update:modelValue="(newFilters) => {
              filters.value = newFilters;
              fetchRecipients();
            }"
          />
        </div>

        <div class="flex-1 space-y-4">
          <div class="flex items-center gap-2 pt-5">
            <Button
              size="icon"
              variant="secondary"
              :disabled="!selectedRecipients.size"
              @click="() => selectedRecipients.clear()">
              <IconUsersMinus class="h-4 w-4"/>
            </Button>

            <Button
              size="icon"
              :disabled="!hasActiveFilters"
              @click="clearFilters"
              variant="outline">
              <FilterXIcon class="h-4 w-4"/>
            </Button>

            <span class="flex-1"/>

            <BatchActions
              :selected-recipients="Array.from(selectedRecipients)"
            />
          </div>

          <div class="overflow-hidden sm:border sm:border-gray-200 sm:rounded-lg sm:dark:border-gray-700 sm:p-5">
            <RecipientTable
              :recipients="recipients.data"
              :selected-recipients="selectedRecipients"
              :is-global-selection="isGlobalSelection"
              :total-recipients="recipients.total"
              :sort-config="sortConfig"
              @toggle-recipient="toggleRecipient"
              @select-all="selectAllRecipients"
              @select-all-global="selectAllRecipientsAcrossPages"
              @clear-global-selection="clearGlobalSelection"
              @sort="toggleSort"
            />
          </div>

          <Pagination
            v-model:current-page="recipients.current_page"
            :links="recipients.links"
            :per-page="recipients.per_page"
            :total="recipients.total"
            :first-page-url="recipients.first_page_url"
            :last-page-url="recipients.last_page_url"
            :next-page-url="recipients.next_page_url"
            :prev-page-url="recipients.prev_page_url"
            :last-page="recipients.last_page"
            @change-page="fetchRecipients"
          />
        </div>
      </div>

      <div v-else class="flex items-center justify-center h-full">
        <EmptyState
          :icon="UserXIcon"
          title="No Recipients Found"
          description="You currently have no recipients.">
          <template #action>
            <GlobalLink
              as="Button"
              :href="route('recipients.create')">
              Add Recipient
            </GlobalLink>
          </template>
        </EmptyState>
      </div>
    </div>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform translate-y-full opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-full opacity-0">
      <div
        v-if="isMobile && selectionStatus.count > 0"
        class="fixed bottom-0 left-0 right-0 bg-primary p-4 flex items-center justify-between z-50">
        <p class="text-sm font-medium text-muted">{{ selectionStatus.text }}</p>

        <div class="flex items-center gap-2">
          <Button
            variant="secondary"
            size="sm"
            @click="deselectAllRecipients">
            Clear selection
          </Button>

          <BatchActions :selected-recipients="Array.from(selectedRecipients)" />
        </div>
      </div>
    </Transition>
  </AppLayout>
</template>

<style scoped>
.bg-card {
  @apply bg-white dark:bg-gray-800;
}

.filter-transition-enter-active,
.filter-transition-leave-active {
  transition: all 0.3s ease;
}

.filter-transition-enter-from,
.filter-transition-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
