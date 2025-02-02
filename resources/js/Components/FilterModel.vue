<script setup lang="ts">
import { type Component, computed } from 'vue'
import {
  Ban,
  CheckCircle2,
  FilterIcon,
  HelpCircle,
  UserMinus,
  XCircle,
  CalendarIcon,
  TagsIcon,
  ActivityIcon,
  XIcon
} from "lucide-vue-next"
import { IconManFilled, IconWomanFilled } from "@tabler/icons-vue"
import { Button } from "@/Components/ui/button"
import { Badge } from "@/Components/ui/badge"
import { Calendar } from "@/Components/ui/v-calendar"
import { ScrollArea } from "@/Components/ui/scroll-area"
import {Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger} from "@/Components/ui/sheet";
import {Separator} from "@/Components/ui/separator";

// Types
interface FilterOption {
  value: string
  label: string
  icon: Component
}

interface FilterValue {
  status: string[]
  gender: string[]
  dateRange: { from: Date | null; to: Date | null } | null
  tags: string[]
  activity: string | null
}

// Props & Emits
const props = defineProps<{
  modelValue: FilterValue
  availableTags?: string[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: FilterValue]
}>()

// Filter Options Data
const genderFilters: FilterOption[] = [
  { value: 'male', label: 'Male', icon: IconManFilled },
  { value: 'female', label: 'Female', icon: IconWomanFilled },
  { value: 'unspecified', label: 'Unknown', icon: HelpCircle }
]

const statusFilters: FilterOption[] = [
  { value: 'active', label: 'Active', icon: CheckCircle2 },
  { value: 'inactive', label: 'Dormant', icon: XCircle },
  { value: 'banned', label: 'Blacklisted', icon: Ban },
  { value: 'unsubscribed', label: 'Opted out', icon: UserMinus }
]

const activityFilters: FilterOption[] = [
  { value: 'active', label: 'Active (Last 30 days)', icon: ActivityIcon },
  { value: 'inactive', label: 'Inactive (30+ days)', icon: XCircle },
  { value: 'never', label: 'Never logged in', icon: Ban }
]

// Methods
const updateFilter = (key: keyof FilterValue, value: any) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value
  })
}

const toggleArrayFilter = (key: keyof FilterValue, value: string) => {
  const currentValues = [...(props.modelValue[key] as string[])]
  const index = currentValues.indexOf(value)

  if (index === -1) {
    currentValues.push(value)
  } else {
    currentValues.splice(index, 1)
  }

  updateFilter(key, currentValues)
}

const clearFilter = (key: keyof FilterValue) => {
  const defaultValues = {
    status: [],
    gender: [],
    dateRange: null,
    tags: [],
    activity: null
  }

  updateFilter(key, defaultValues[key])
}

const clearAllFilters = () => {
  emit('update:modelValue', {
    status: [],
    gender: [],
    dateRange: null,
    tags: [],
    activity: null
  })
}

// Computed
const selectedFiltersCount = computed(() => {
  let count = 0
  const { status, gender, dateRange, tags, activity } = props.modelValue

  if (status.length) count += 1
  if (gender.length) count += 1
  if (dateRange) count += 1
  if (tags.length) count += 1
  if (activity) count += 1

  return count
})

const hasActiveFilters = computed(() => selectedFiltersCount.value > 0)
</script>

<template>
  <!-- Desktop Filter Sidebar -->
  <div class="hidden sm:block space-y-4 max-w-48">
    <div class="flex items-center justify-between">
      <h4 class="font-medium text-sm flex items-center gap-2">
        <FilterIcon class="w-4 h-4 stroke-2"/>
        <span>Filters</span>
      </h4>

      <div class="flex items-center gap-2">
        <span
          v-if="selectedFiltersCount"
          class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full">
          {{ selectedFiltersCount }}
        </span>
        <Button
          v-if="hasActiveFilters"
          variant="ghost"
          size="sm"
          @click="clearAllFilters">
          Clear all
        </Button>
      </div>
    </div>

    <ScrollArea class="h-[calc(100vh-12rem)]">
      <div class="space-y-6 pr-4">
        <!-- Status Filters -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <h5 class="text-sm font-medium">Status</h5>
            <Button
              v-if="modelValue.status.length"
              variant="ghost"
              size="sm"
              @click="clearFilter('status')">
              Clear
            </Button>
          </div>

          <div class="space-y-1">
            <Button
              v-for="filter in statusFilters"
              :key="filter.value"
              variant="ghost"
              class="w-full justify-start gap-2"
              :class="{ 'bg-primary/10 text-primary': modelValue.status.includes(filter.value) }"
              @click="toggleArrayFilter('status', filter.value)">
              <component :is="filter.icon" class="w-4 h-4" />
              {{ filter.label }}
            </Button>
          </div>
        </div>

        <!-- Gender Filters -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <h5 class="text-sm font-medium">Gender</h5>
            <Button
              v-if="modelValue.gender.length"
              variant="ghost"
              size="sm"
              @click="clearFilter('gender')">
              Clear
            </Button>
          </div>

          <div class="space-y-1">
            <Button
              v-for="filter in genderFilters"
              :key="filter.value"
              variant="ghost"
              class="w-full justify-start gap-2"
              :class="{ 'bg-primary/10 text-primary': modelValue.gender.includes(filter.value) }"
              @click="toggleArrayFilter('gender', filter.value)">
              <component :is="filter.icon" class="w-4 h-4" />
              {{ filter.label }}
            </Button>
          </div>
        </div>

        <!-- Date Range Filter -->
<!--        <div class="space-y-2">-->
<!--          <div class="flex items-center justify-between">-->
<!--            <h5 class="text-sm font-medium">Date Range</h5>-->
<!--            <Button-->
<!--              v-if="modelValue.dateRange"-->
<!--              variant="ghost"-->
<!--              size="sm"-->
<!--              @click="clearFilter('dateRange')"-->
<!--            >-->
<!--              Clear-->
<!--            </Button>-->
<!--          </div>-->
<!--          <Popover>-->
<!--            <PopoverTrigger as-child>-->
<!--              <Button-->
<!--                variant="outline"-->
<!--                class="w-full justify-start text-left font-normal"-->
<!--                :class="{ 'text-primary': modelValue.dateRange }"-->
<!--              >-->
<!--                <CalendarIcon class="mr-2 h-4 w-4" />-->
<!--                <span v-if="modelValue.dateRange">-->
<!--                  {{ modelValue.dateRange.from?.toLocaleDateString() }} - -->
<!--                  {{ modelValue.dateRange.to?.toLocaleDateString() }}-->
<!--                </span>-->
<!--                <span v-else>Pick a date range</span>-->
<!--              </Button>-->
<!--            </PopoverTrigger>-->
<!--            <PopoverContent class="w-auto p-0" align="start">-->
<!--              <Calendar-->
<!--                v-model="modelValue.dateRange"-->
<!--                mode="range"-->
<!--                class="border-0"-->
<!--              />-->
<!--            </PopoverContent>-->
<!--          </Popover>-->
<!--        </div>-->

        <!-- Tags Filter -->
        <div v-if="availableTags?.length" class="space-y-2">
          <div class="flex items-center justify-between">
            <h5 class="text-sm font-medium">Tags</h5>
            <Button
              v-if="modelValue.tags.length"
              variant="ghost"
              size="sm"
              @click="clearFilter('tags')">
              Clear
            </Button>
          </div>
          <div class="flex flex-wrap gap-1">
            <Badge
              v-for="tag in availableTags"
              :key="tag"
              variant="outline"
              class="cursor-pointer"
              :class="{ 'bg-primary/10 text-primary': modelValue.tags.includes(tag) }"
              @click="toggleArrayFilter('tags', tag)">
              {{ tag }}
            </Badge>
          </div>
        </div>

        <!-- Activity Filter -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <h5 class="text-sm font-medium">Activity</h5>
            <Button
              v-if="modelValue.activity"
              variant="ghost"
              size="sm"
              @click="clearFilter('activity')">
              Clear
            </Button>
          </div>

          <div class="space-y-1">
            <Button
              v-for="filter in activityFilters"
              :key="filter.value"
              variant="ghost"
              class="w-full justify-start gap-2"
              :class="{ 'bg-primary/10 text-primary': modelValue.activity === filter.value }"
              @click="updateFilter('activity', modelValue.activity === filter.value ? null : filter.value)">
              <component :is="filter.icon" class="w-4 h-4" />
              {{ filter.label }}
            </Button>
          </div>
        </div>
      </div>
    </ScrollArea>
  </div>

  <!-- Mobile Filter Sheet -->
  <Sheet>
    <SheetTrigger asChild>
      <Button
        size="icon"
        variant="outline"
        class="relative sm:hidden">
        <FilterIcon class="w-4 h-4" />
        <span
          v-if="selectedFiltersCount"
          class="absolute -top-1 -right-1 w-4 h-4 text-[10px] flex items-center justify-center bg-primary text-primary-foreground rounded-full">
          {{ selectedFiltersCount }}
        </span>
      </Button>
    </SheetTrigger>

    <SheetContent side="bottom" class="h-[85vh]">
      <SheetHeader class="space-y-4">
        <div class="flex items-center justify-between">
          <SheetTitle class="flex items-center gap-2">
            <FilterIcon class="w-4 h-4" />
            Filters
          </SheetTitle>
          <Button
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="clearAllFilters">
            Clear all
          </Button>
        </div>
      </SheetHeader>

      <ScrollArea class="h-full mt-6">
        <div class="space-y-6 pb-8">
          <!-- Status Filters -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">Status</h3>
              <Button
                v-if="modelValue.status.length"
                variant="ghost"
                size="sm"
                @click="clearFilter('status')">
                Clear
              </Button>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <Button
                v-for="filter in statusFilters"
                :key="filter.value"
                variant="outline"
                :class="{ 'bg-primary/10 text-primary border-primary': modelValue.status.includes(filter.value) }"
                @click="toggleArrayFilter('status', filter.value)">
                <component :is="filter.icon" class="w-4 h-4 mr-2" />
                {{ filter.label }}
              </Button>
            </div>
          </div>

          <Separator />

          <!-- Gender Filters -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">Gender</h3>
              <Button
                v-if="modelValue.gender.length"
                variant="ghost"
                size="sm"
                @click="clearFilter('gender')">
                Clear
              </Button>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <Button
                v-for="filter in genderFilters"
                :key="filter.value"
                variant="outline"
                :class="{ 'bg-primary/10 text-primary border-primary': modelValue.gender.includes(filter.value) }"
                @click="toggleArrayFilter('gender', filter.value)">
                <component :is="filter.icon" class="w-4 h-4 mr-2" />
                {{ filter.label }}
              </Button>
            </div>
          </div>

          <Separator />

          <!-- Tags Filter -->
          <div v-if="availableTags?.length" class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">Tags</h3>
              <Button
                v-if="modelValue.tags.length"
                variant="ghost"
                size="sm"
                @click="clearFilter('tags')">
                Clear
              </Button>
            </div>
            <div class="flex flex-wrap gap-2">
              <Badge
                v-for="tag in availableTags"
                :key="tag"
                variant="outline"
                class="cursor-pointer text-sm"
                :class="{ 'bg-primary/10 text-primary border-primary': modelValue.tags.includes(tag) }"
                @click="toggleArrayFilter('tags', tag)">
                {{ tag }}
              </Badge>
            </div>
          </div>

          <Separator />

          <!-- Activity Filter -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">Activity</h3>
              <Button
                v-if="modelValue.activity"
                variant="ghost"
                size="sm"
                @click="clearFilter('activity')">
                Clear
              </Button>
            </div>
            <div class="grid grid-cols-1 gap-2">
              <Button
                v-for="filter in activityFilters"
                :key="filter.value"
                variant="outline"
                :class="{ 'bg-primary/10 text-primary border-primary': modelValue.activity === filter.value }"
                @click="updateFilter('activity', modelValue.activity === filter.value ? null : filter.value)">
                <component :is="filter.icon" class="w-4 h-4 mr-2" />
                {{ filter.label }}
              </Button>
            </div>
          </div>
        </div>
      </ScrollArea>
    </SheetContent>
  </Sheet>
</template>

<style scoped>
.sticky {
  @apply bg-background;
}
</style>
