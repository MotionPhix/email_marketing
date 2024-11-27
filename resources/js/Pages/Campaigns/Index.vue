<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link} from '@inertiajs/vue3'
import {Button} from "@/Components/ui/button";
import {
  ExternalLinkIcon,
  DotsHorizontalIcon,
  FileTextIcon,
  Pencil1Icon,
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
} from '@/Components/ui/dropdown-menu'
import {
  Trash2Icon,
  SendHorizontalIcon,
  Plus,
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

const {campaigns} = defineProps({
  campaigns: Object,
});

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

      <div v-if="campaigns.data.length" class="mt-4 border rounded-lg overflow-hidden">

        <Table>
          <TableCaption class="my-0">

            <section class="flex justify-end p-4">
              <Pagination
                :items-per-page="campaigns.per_page"
                v-slot="{ page }" :total="campaigns.total"
                :sibling-count="1" show-edges :default-page="campaigns.current_page">
                <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                  <PaginationFirst class="size-7"/>
                  <PaginationPrev class="size-7"/>

                  <template v-for="(item, index) in items">

                    <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                      <Button class="size-7 p-0" :variant="item.value === page ? 'default' : 'outline'">
                        {{ item.value }}
                      </Button>
                    </PaginationListItem>

                    <PaginationEllipsis v-else :key="item.type" :index="index"/>

                  </template>

                  <PaginationNext class="size-7"/>
                  <PaginationLast class="size-7"/>

                </PaginationList>

              </Pagination>

            </section>

          </TableCaption>

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

              <TableHead/>

            </TableRow>

          </TableHeader>

          <TableBody>
            <TableRow v-for="campaign in campaigns.data" :key="campaign.id">
              <TableCell class="font-medium">
                {{ campaign.title }}
              </TableCell>

              <TableCell class="capitalize">{{ campaign.status }}</TableCell>

              <TableCell>{{ campaign.scheduled_at ?? 'Not scheduled' }}</TableCell>

              <TableCell class="text-right">
                {{ campaign.recipients_count }}
              </TableCell>

              <TableCell>

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

                      <DropdownMenuItem as-child v-if="campaign.template_id">
                        <GlobalLink
                          class="flex w-full text-left" as="button"
                          :href="route('templates.preview', campaign.template_id)">
                          <ExternalLinkIcon class="mr-2 h-4 w-4"/>
                          <span>Preview</span>
                        </GlobalLink>
                      </DropdownMenuItem>

                      <DropdownMenuItem
                        v-if="campaign.audience_id"
                        as-child>
                        <Link
                          method="post"
                          class="flex w-full text-left" as="button"
                          :href="route('campaigns.send', campaign.uuid)">
                          <SendHorizontalIcon class="mr-2 h-4 w-4"/>
                          <span class="flex-1">Send</span>
                        </Link>
                      </DropdownMenuItem>

                      <DropdownMenuItem as-child>
                        <Link
                          class="flex w-full text-left" as="button"
                          :href="route('campaigns.show', campaign.uuid)">
                          <FileTextIcon class="mr-2 h-4 w-4"/>
                          <span class="flex-1">In detail</span>
                        </Link>
                      </DropdownMenuItem>

                    </DropdownMenuGroup>

                    <DropdownMenuSeparator/>

                    <DropdownMenuGroup>

                      <DropdownMenuItem as-child>
                        <Link
                          class="flex w-full text-left" as="button"
                          :href="route('campaigns.edit', campaign.uuid)">
                          <Pencil1Icon class="mr-2 h-4 w-4"/>
                          <span class="flex-1">Edit</span>
                        </Link>
                      </DropdownMenuItem>

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
        </Table>

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
