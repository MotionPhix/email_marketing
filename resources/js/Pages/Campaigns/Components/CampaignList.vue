<script setup lang="ts">
import {ref, computed, watch} from 'vue'
import {Link} from '@inertiajs/vue3'
import {format} from 'date-fns'
import {
  IconFile,
  IconMail,
  IconEye,
  IconEdit,
  IconTrash,
  IconDotsVertical,
  IconDownload,
} from '@tabler/icons-vue'
import type {Campaign} from '@/types'
import DateRangePicker from '@/Components/DateRangePicker.vue'
import {useDebounceFn} from "@vueuse/core";

interface Props {
  campaigns: Campaign[]
  selected: number[]
  filters: {
    search: string
    status: string
    date_from: string
    date_to: string
    sort_by: string
    sort_direction: 'asc' | 'desc'
  }
}

interface Emits {
  (e: 'update:selected', value: number[]): void

  (e: 'delete', id: number): void

  (e: 'filter', filters: Partial<Props['filters']>): void

  (e: 'export'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const searchQuery = ref(props.filters.search)
const statusFilter = ref(props.filters.status)
const dateRange = ref([props.filters.date_from, props.filters.date_to])
const allSelected = ref(false)

const statusOptions = [
  {value: 'all', label: 'All Status'},
  {value: 'draft', label: 'Draft'},
  {value: 'scheduled', label: 'Scheduled'},
  {value: 'sending', label: 'Sending'},
  {value: 'sent', label: 'Sent'},
  {value: 'failed', label: 'Failed'}
]

const columns = [
  {key: 'name', label: 'Campaign', sortable: true},
  {key: 'status', label: 'Status', sortable: true},
  {key: 'recipient_count', label: 'Recipients', sortable: true},
  {key: 'created_at', label: 'Date', sortable: true}
]

const handleSort = (column: string) => {
  if (!columns.find(col => col.key === column)?.sortable) return

  const direction = props.filters.sort_by === column && props.filters.sort_direction === 'asc'
    ? 'desc'
    : 'asc'

  emit('filter', {
    sort_by: column,
    sort_direction: direction
  })
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    emit('update:selected', [])
  } else {
    emit('update:selected', props.campaigns.map(c => c.id))
  }
  allSelected.value = !allSelected.value
}

const toggleSelect = (id: number) => {
  const selected = new Set(props.selected)
  if (selected.has(id)) {
    selected.delete(id)
  } else {
    selected.add(id)
  }
  emit('update:selected', Array.from(selected))
}

const formatDate = (date: string) => {
  return format(new Date(date), 'MMM d, yyyy h:mm a')
}

watch(searchQuery, useDebounceFn((value) => {
  emit('filter', {search: value})
}, 300))

watch(statusFilter, (value) => {
  emit('filter', {status: value === 'all' ? undefined : value})
})

watch(dateRange, ([from, to]) => {
  emit('filter', {
    date_from: from,
    date_to: to
  })
})
</script>

<template>
  <div class="space-y-4">
    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 gap-4">
        <Input
          v-model="searchQuery"
          type="search"
          placeholder="Search campaigns..."
          class="max-w-xs"
        />

        <div>
          <Select
            v-model="statusFilter">
            <SelectTrigger>
              <SelectValue placeholder="Select status"/>
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="option in statusOptions"
                :key="option.value"
                :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DateRangePicker
          v-model="dateRange"
          class="w-[280px]"
        />
      </div>

      <div class="flex items-center gap-2">
        <Button
          v-if="selected.length > 0"
          variant="outline"
          @click="emit('delete', selected)">
          <IconTrash class="mr-2 h-4 w-4"/>
          Delete Selected
        </Button>

        <Button
          variant="outline"
          @click="emit('export')">
          <IconDownload class="mr-2 h-4 w-4"/>
          Export
        </Button>
      </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block">
      <Card>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[50px]">
                <Checkbox
                  :checked="allSelected"
                  @update:checked="toggleSelectAll"
                />
              </TableHead>

              <TableHead
                v-for="column in columns"
                :key="column.key"
                :class="[
                  column.sortable && 'cursor-pointer select-none',
                  column.key === 'actions' && 'w-[100px]'
                ]"
                @click="handleSort(column.key)">
                <div class="flex items-center space-x-2">
                  <span>{{ column.label }}</span>
                  <span v-if="column.sortable && props.filters.sort_by === column.key">
                    {{ props.filters.sort_direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </div>
              </TableHead>
              <TableHead class="w-[100px]"/>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="campaign in campaigns" :key="campaign.id">
              <TableCell>
                <Checkbox
                  :checked="selected.includes(campaign.id)"
                  @update:checked="toggleSelect(campaign.id)"
                />
              </TableCell>

              <TableCell>
                <div class="flex items-center gap-3">
                  <component
                    :is="campaign.status === 'draft' ? IconFile : IconMail"
                    class="h-5 w-5"
                  />
                  <div>
                    <Link
                      :href="route('campaigns.show', campaign.id)"
                      class="font-medium hover:underline">
                      {{ campaign.name }}
                    </Link>

                    <div class="text-sm text-muted-foreground">
                      {{ campaign.subject }}
                    </div>
                  </div>
                </div>
              </TableCell>

              <TableCell>
                <Badge :variant="campaign.status">
                  {{ campaign.status }}
                </Badge>
              </TableCell>

              <TableCell>
                {{ campaign.recipient_count.toLocaleString() }}
              </TableCell>

              <TableCell>
                <div v-if="campaign.scheduled_at">
                  Scheduled: {{ formatDate(campaign.scheduled_at) }}
                </div>

                <div v-else-if="campaign.sent_at">
                  Sent: {{ formatDate(campaign.sent_at) }}
                </div>

                <div v-else>
                  Created: {{ formatDate(campaign.created_at) }}
                </div>
              </TableCell>

              <TableCell>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                      <IconDotsVertical class="h-4 w-4"/>
                    </Button>
                  </DropdownMenuTrigger>

                  <DropdownMenuContent align="end">

                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.show', campaign.id)">
                        <IconEye class="mr-2 h-4 w-4"/>
                        View Details
                      </Link>
                    </DropdownMenuItem>

                    <DropdownMenuItem asChild>
                      <Link :href="route('campaigns.edit', campaign.id)">
                        <IconEdit class="mr-2 h-4 w-4"/>
                        Edit
                      </Link>
                    </DropdownMenuItem>

                    <DropdownMenuSeparator/>

                    <DropdownMenuItem
                      v-if="campaign.status === 'draft'"
                      @click="emit('delete', campaign.id)"
                      class="text-destructive">
                      <IconTrash class="mr-2 h-4 w-4"/>
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </Card>
    </div>

    <!-- Mobile Card View -->
    <div class="space-y-4 md:hidden">
      <Card v-for="campaign in campaigns" :key="campaign.id">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <div class="flex items-center space-x-2">
            <Checkbox
              :checked="selected.includes(campaign.id)"
              @change="toggleSelect(campaign.id)"
            />
            <CardTitle class="text-base">{{ campaign.name }}</CardTitle>
          </div>

          <Badge :variant="campaign.status">
            {{ campaign.status }}
          </Badge>
        </CardHeader>

        <CardContent>
          <div class="grid gap-4">
            <div class="text-sm text-muted-foreground">
              {{ campaign.subject }}
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <div class="text-muted-foreground">Recipients</div>
                <div class="font-medium">
                  {{ campaign.recipient_count.toLocaleString() }}
                </div>
              </div>

              <div>
                <div class="text-muted-foreground">Date</div>
                <div class="font-medium">
                  {{ formatDate(campaign.scheduled_at || campaign.sent_at || campaign.created_at) }}
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-2">
              <Button
                variant="ghost"
                size="sm"
                asChild>
                <Link :href="route('campaigns.show', campaign.id)">
                  <IconEye class="mr-2 h-4 w-4"/>
                  View
                </Link>
              </Button>

              <Button
                variant="ghost"
                size="sm"
                asChild>
                <Link :href="route('campaigns.edit', campaign.id)">
                  <IconEdit class="mr-2 h-4 w-4"/>
                  Edit
                </Link>
              </Button>

              <Button
                v-if="campaign.status === 'draft'"
                variant="ghost"
                size="sm"
                @click="emit('delete', campaign.id)"
                class="text-destructive">
                <IconTrash class="mr-2 h-4 w-4"/>
                Delete
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
