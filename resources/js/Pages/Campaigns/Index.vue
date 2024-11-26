<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link} from '@inertiajs/vue3'
import {Button} from "@/Components/ui/button";
import {Input} from '@/Components/ui/input'
import {
  ExternalLinkIcon,
  DotsHorizontalIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  ChevronDownIcon
} from "@radix-icons/vue";
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  DropdownMenuCheckboxItem,
} from '@/Components/ui/dropdown-menu'
import {
  Trash2Icon,
  SendHorizontalIcon,
  Plus, ArrowUpDown,
} from 'lucide-vue-next'
import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/Components/ui/pagination'
import {TableFooter} from "@/Components/ui/table";
import type {
  ColumnDef,
  ColumnFiltersState,
  ExpandedState,
  SortingState,
  VisibilityState,
} from '@tanstack/vue-table'
import {
  FlexRender,
  getCoreRowModel,
  getExpandedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import { valueUpdater } from '@/lib/utils'
import {h, ref} from "vue";
import {Checkbox} from "@/Components/ui/checkbox";
import DropdownAction from "@/Pages/Campaigns/DropdownAction.vue";

export interface Campaign {
  id: number
  uuid: string
  title: string
  status: 'draft' | 'processing' | 'sent' | 'failed'
  scheduled_at: string|null
  recipients_count: number
  template_id: number
}

const {campaigns} = defineProps({
  campaigns: Object,
});

const data: Campaign[] = campaigns.data

const columns: ColumnDef<Campaign>[] = [
  {
    id: 'select',
    header: ({ table }) => h(Checkbox, {
      'checked': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
      'onUpdate:checked': value => table.toggleAllPageRowsSelected(!!value),
      'ariaLabel': 'Select all',
    }),
    cell: ({ row }) => h(Checkbox, {
      'checked': row.getIsSelected(),
      'onUpdate:checked': value => row.toggleSelected(!!value),
      'ariaLabel': 'Select row',
    }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'title',
    header: ({ column }) => {
      return h(Button, {
        variant: 'outline',
        onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
      }, () => ['Campaign', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
    },
    cell: ({ row }) => h('div', { class: 'lowercase' }, row.getValue('title')),
  },
  {
    accessorKey: 'status',
    header: 'Status',
    cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue('status')),
  },
  {
    accessorKey: 'scheduled_at',
    header: () => h('div', { class: 'text-right' }, 'Schedule'),
    cell: ({ row }) => {
      /*const amount = Number.parseFloat(row.getValue('amount'))

      // Format the amount as a dollar amount
      const formatted = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
      }).format(amount)*/

      return h('div', { class: 'text-right font-medium' }, row.getValue('scheduled_at'))
    },
  },
  {
    accessorKey: 'recipients_count ',
    header: 'Recipients',
    cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue('recipients_count ')),
  },
  {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
      const payment = row.original

      return h('div', { class: 'relative' }, h(DropdownAction, {
        payment,
        onExpand: row.toggleExpanded,
      }))
    },
  },
]

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})

const table = useVueTable({
  data,
  columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
  onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
    get expanded() { return expanded.value },
  },
})

console.log(campaigns);

</script>

<template>
  <AppLayout title="Campaigns">

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Campaigns
      </h2>
    </template>

    <template #action>
      <Button as-child>
        <Link as="button" :href="route('campaigns.create')">
          <span>New</span>
          <Plus class="size-5"/>
        </Link>
      </Button>
    </template>

    <div class="py-12">

<!--      start-->
      <div class="w-full">
        <div class="flex gap-2 items-center py-4">
          <Input
            class="max-w-sm"
            placeholder="Filter campaigns..."
            :model-value="table.getColumn('title')?.getFilterValue() as string"
            @update:model-value=" table.getColumn('title')?.setFilterValue($event)"
          />
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="ml-auto">
                Columns <ChevronDownIcon class="ml-2 h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end">
              <DropdownMenuCheckboxItem
                v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                :key="column.id"
                class="capitalize"
                :checked="column.getIsVisible()"
                @update:checked="(value) => {
              column.toggleVisibility(!!value)
            }"
              >
                {{ column.id }}
              </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                  <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                </TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <template v-if="table.getRowModel().rows?.length">
                <template v-for="row in table.getRowModel().rows" :key="row.id">
                  <TableRow :data-state="row.getIsSelected() && 'selected'">
                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                      <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                    </TableCell>
                  </TableRow>

                  <TableRow v-if="row.getIsExpanded()">
                    <TableCell :colspan="row.getAllCells().length">
                      {{ JSON.stringify(row.original) }}
                    </TableCell>
                  </TableRow>
                </template>
              </template>

              <TableRow v-else>
                <TableCell
                  :colspan="columns.length"
                  class="h-24 text-center"
                >
                  No results.
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div class="flex items-center justify-end space-x-2 py-4">
          <div class="flex-1 text-sm text-muted-foreground">
            {{ table.getFilteredSelectedRowModel().rows.length }} of
            {{ table.getFilteredRowModel().rows.length }} row(s) selected.
          </div>

          <div class="space-x-2">
            <Button
              variant="outline"
              size="sm"
              :disabled="!table.getCanPreviousPage()"
              @click="table.previousPage()"
            >
              Previous
            </Button>

            <Button
              variant="outline"
              size="sm"
              :disabled="!table.getCanNextPage()"
              @click="table.nextPage()"
            >
              Next
            </Button>
          </div>
        </div>
      </div>
<!--      end-->

      <div v-if="campaigns.data.length" class="mt-4">
        <Table>
          <TableCaption>A list of your campaigns.</TableCaption>

          <TableHeader>
            <TableRow>
              <TableHead>
                Campaign
              </TableHead>

              <TableHead class="w-[100px]">Status</TableHead>

              <TableHead>Schedule</TableHead>

              <TableHead class="text-right">
                Recipients
              </TableHead>

              <TableHead></TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="campaign in campaigns.data" :key="campaign.id">
              <TableCell class="font-medium">
                {{ campaign.title }}
              </TableCell>

              <TableCell class="capitalize">{{ campaign.status }}</TableCell>

              <TableCell>{{ campaign.scheduled_at }}</TableCell>

              <TableCell class="text-right">
                {{ campaign?.audience?.recipients.length }}
              </TableCell>

              <TableCell>
                <!--                <Button-->
                <!--                  as-child>-->
                <!--                  <Link as="button" :href="route('campaigns.send', campaign.id)">-->
                <!--                    Send Now-->
                <!--                  </Link>-->
                <!--                </Button>-->

                <!--                <Button-->
                <!--                  as-child>-->
                <!--                  <Link as="button" :href="route('campaigns.send', campaign.id)">-->
                <!--                    Send Now-->
                <!--                  </Link>-->
                <!--                </Button>-->

                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="icon">
                      <DotsHorizontalIcon/>
                    </Button>
                  </DropdownMenuTrigger>

                  <DropdownMenuContent :side-offset="-4" align="end" class="w-40">
                    <DropdownMenuLabel>Actions</DropdownMenuLabel>

                    <DropdownMenuSeparator/>

                    <DropdownMenuGroup>

                      <DropdownMenuItem as-child>
                        <Link
                          method="post"
                          class="flex w-full text-left" as="button"
                          :href="route('campaigns.send', campaign.uuid)">
                          <SendHorizontalIcon class="mr-2 h-4 w-4"/>
                          <span class="flex-1">Send</span>
                        </Link>
                      </DropdownMenuItem>

                      <DropdownMenuItem as-child>
                        <GlobalLink
                          class="flex w-full text-left" as="button"
                          :href="route('templates.preview', campaign.template.uuid)">
                          <ExternalLinkIcon class="mr-2 h-4 w-4"/>
                          <span>Preview</span>
                        </GlobalLink>
                      </DropdownMenuItem>

                    </DropdownMenuGroup>

                    <DropdownMenuSeparator/>

                    <DropdownMenuGroup>
                      <DropdownMenuItem>
                        <Trash2Icon class="mr-2 h-4 w-4"/>
                        <span>Delete</span>
                      </DropdownMenuItem>
                    </DropdownMenuGroup>
                  </DropdownMenuContent>
                </DropdownMenu>

              </TableCell>
            </TableRow>
          </TableBody>

          <TableFooter class="py-2">
            <Pagination
              :items-per-page="campaigns.per_page"
              v-slot="{ page }" :total="campaigns.total"
              :sibling-count="1" show-edges :default-page="campaigns.current_page">
              <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                <PaginationFirst class="size-7" />
                <PaginationPrev class="size-7" />

                <template v-for="(item, index) in items">

                  <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                    <Button class="size-7 p-0" :variant="item.value === page ? 'default' : 'outline'">
                      {{ item.value }}
                    </Button>
                  </PaginationListItem>

                  <PaginationEllipsis v-else :key="item.type" :index="index" />
                </template>

                <PaginationNext class="size-7" />
                <PaginationLast class="size-7" />
              </PaginationList>
            </Pagination>
          </TableFooter>
        </Table>



        <!-- Pagination links -->
<!--        <div v-if="campaigns.links.length > 0">-->
<!--          <Button size="icon" as-child v-for="link in campaigns.links" :key="link.label">-->
<!--            <Link-->
<!--              as="button"-->
<!--              class="flex items-center gap-4"-->
<!--              :disabled="link.url === null"-->
<!--              :href="String(link.url)">-->
<!--              <span v-if="link.active">{{ link.label }}</span>-->

<!--              <component-->
<!--                v-else-->
<!--                :is="link.label.startsWith('Next') ? ArrowRightIcon : ArrowLeftIcon"-->
<!--              />-->
<!--            </Link>-->
<!--          </Button>-->
<!--        </div>-->
      </div>

      <div v-else>

        <h2 class="text-2xl">
          No campaigns
        </h2>

        <p class="text-gray-500 mb-4">
          You don't have any campaigns yet.
        </p>

        <Button as-child>

          <Link
            as="button"
            :href="route('campaigns.create')">
            Create campaign
          </Link>

        </Button>

      </div>

    </div>

  </AppLayout>
</template>
