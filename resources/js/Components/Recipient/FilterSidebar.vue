<script setup lang="ts">
import {type Component, computed} from 'vue'
import {
  Ban,
  CheckCircle2,
  FilterIcon,
  HelpCircle,
  UserMinus,
  XCircle
} from "lucide-vue-next"
import {IconManFilled, IconWomanFilled} from "@tabler/icons-vue"
import {
  Command,
  CommandGroup,
  CommandItem,
  CommandList,
  CommandSeparator
} from '@/Components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Button } from "@/Components/ui/button"

// Types
interface FilterOption {
  value: string
  label: string
  icon: Component
}

// Props & Emits
const props = defineProps<{
  modelValue: Record<string, any>
}>()

const emit = defineEmits<{
  'update:modelValue': [value: Record<string, any>]
}>()

// Filter Options Data
const genderFilters: FilterOption[] = [
  {
    value: 'male',
    label: 'Male',
    icon: IconManFilled
  },
  {
    value: 'female',
    label: 'Female',
    icon: IconWomanFilled
  },
  {
    value: 'unspecified',
    label: 'Unknown',
    icon: HelpCircle
  }
]

const statusFilters: FilterOption[] = [
  {
    value: 'active',
    label: 'Active',
    icon: CheckCircle2
  },
  {
    value: 'inactive',
    label: 'Dormant',
    icon: XCircle
  },
  {
    value: 'banned',
    label: 'Blacklisted',
    icon: Ban
  },
  {
    value: 'unsubscribed',
    label: 'Opted out',
    icon: UserMinus
  }
]

// Methods
const updateFilter = (key: string, value: any) => {
  emit("update:modelValue", { ...props.modelValue, [key]: value })
}

// Computed
const selectedFiltersCount = computed(() => {
  return Object.values(props.modelValue).filter(Boolean).length
})
</script>

<template>
  <!-- Desktop Filter Sidebar -->
  <div class="hidden sm:block space-y-4">
    <div class="flex items-center justify-between">
      <h4 class="font-medium text-sm flex items-center gap-2">
        <FilterIcon class="w-4 h-4 stroke-2"/>
        <span>Filters</span>
      </h4>
      <span
        v-if="selectedFiltersCount"
        class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full"
      >
        {{ selectedFiltersCount }}
      </span>
    </div>

    <div class="sticky top-18">
      <Command
        multiple
        class="rounded-lg border shadow-sm"
        @update:modelValue="updateFilter('status', $event)"
        v-model="modelValue.status">
        <CommandList>
          <!-- Gender Filters -->
          <CommandGroup heading="Filter by gender">
            <CommandItem
              v-for="filter in genderFilters"
              :key="filter.value"
              :value="filter.value"
              class="flex items-center gap-2 cursor-pointer"
            >
              <component
                :is="filter.icon"
                class="w-4 h-4"
              />
              <span>{{ filter.label }}</span>
            </CommandItem>
          </CommandGroup>

          <CommandSeparator />

          <!-- Status Filters -->
          <CommandGroup heading="Filter by status">
            <CommandItem
              v-for="filter in statusFilters"
              :key="filter.value"
              :value="filter.value"
              class="flex items-center gap-2 cursor-pointer">
              <component
                :is="filter.icon"
                class="w-4 h-4"
              />
              <span>{{ filter.label }}</span>
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </div>
  </div>

  <!-- Mobile Filter Popover -->
  <Popover>
    <PopoverTrigger as-child>
      <Button
        size="icon"
        variant="outline"
        class="relative sm:hidden">
        <FilterIcon class="w-4 h-4" />
        <span
          v-if="selectedFiltersCount"
          class="absolute -top-1 -right-1 w-4 h-4 text-[10px] flex items-center justify-center bg-primary text-primary-foreground rounded-full"
        >
          {{ selectedFiltersCount }}
        </span>
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-72 p-0" align="end">
      <Command
        multiple
        @update:modelValue="updateFilter('status', $event)"
        v-model="modelValue.status">
        <CommandList>
          <!-- Gender Filters -->
          <CommandGroup heading="Filter by gender">
            <CommandItem
              v-for="filter in genderFilters"
              :key="filter.value"
              :value="filter.value"
              class="flex items-center gap-2 cursor-pointer"
            >
              <svg
                class="w-4 h-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                v-html="filter.icon"
              />
              <span>{{ filter.label }}</span>
            </CommandItem>
          </CommandGroup>

          <CommandSeparator />

          <!-- Status Filters -->
          <CommandGroup heading="Filter by status">
            <CommandItem
              v-for="filter in statusFilters"
              :key="filter.value"
              :value="filter.value"
              class="flex items-center gap-2 cursor-pointer"
            >
              <svg
                class="w-4 h-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                v-html="filter.icon"
              />
              <span>{{ filter.label }}</span>
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>

<style scoped>
.sticky {
  @apply bg-background;
}

:deep(.command-input) {
  @apply border-none shadow-none;
}

:deep(.command-group) {
  @apply px-1;
}

:deep(.command-separator) {
  @apply my-2;
}

:deep(.command-item) {
  @apply rounded-md px-2 py-1.5 text-sm cursor-pointer transition-colors;
}

:deep(.command-item[data-selected="true"]) {
  @apply bg-primary/10 text-primary;
}
</style>
