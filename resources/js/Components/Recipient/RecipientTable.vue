<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from "@/Components/ui/table";
import {Button} from "@/Components/ui/button";
import {Badge} from "@/Components/ui/badge";
import {Link} from '@inertiajs/vue3'

const {recipients, selectedRecipients} = defineProps<{
  recipients: Array<{ id: number; uuid: string; name: string; email: string, gender: string, status: string }>;
  selectedRecipients: Set<number>;
}>();

const emit = defineEmits(["toggle-recipient", "select-all"]);

const toggleAll = (selectAll: boolean) => {
  emit("select-all", selectAll);
};

const matchedStatus = (status) => {
  const statusMap = {
    banned: { label: 'Blacklisted', class: 'bg-red-100 text-red-800' },
    active: { label: 'Active', class: 'bg-green-100 text-green-800' },
    inactive: { label: 'Dormant', class: 'bg-gray-100 text-green-800' },
    unsubscribed: { label: 'Opted out', class: 'bg-yellow-100 text-yellow-800' },
  };

  return statusMap[status] || 'Unknown status';
};
</script>

<template>
  <Table class="w-full">
    <TableHeader>
      <TableRow>
        <TableHead>
          <input
            type="checkbox"
            class="w-6 h-6 rounded-md"
            @change="toggleAll($event.target.checked)"
            :checked="recipients.every(({ id }) => selectedRecipients.has(id))"
          />
        </TableHead>
        <TableHead>Name</TableHead>
        <TableHead>Gender</TableHead>
        <TableHead class="text-right">Status</TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>

      <TableRow v-for="recipient in recipients" :key="recipient.id" class="group">
        <TableCell>
          <input
            type="checkbox"
            class="w-6 h-6 rounded-md"
            :checked="selectedRecipients.has(recipient.id)"
            @change="$emit('toggle-recipient', recipient.id)"
          />
        </TableCell>

        <TableCell class="grid">
          <strong>
            {{ recipient.name }}
          </strong>
          <span class="text-sm text-muted-foreground">
            {{ recipient.email }}
          </span>
        </TableCell>

        <TableCell class="capitalize">
          {{ recipient.gender }}
        </TableCell>

        <TableCell class="text-right">
          <Badge
            class="group-hover:hidden rounded-sm px-2 py-1"
            :class="matchedStatus(recipient.status).class">
            {{ matchedStatus(recipient.status).label }}
          </Badge>

          <div class="hidden group-hover:inline-flex justify-end gap-1">
            <Button
              as-child
              size="icon"
              :href="route('recipients.edit', recipient.uuid)"
              class="bg-purple-500 h-8 w-8">
              <GlobalLink as="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                  <path d="M6.53792 2.32172C6.69664 1.89276 7.30336 1.89276 7.46208 2.32172L8.1735 4.2443C8.27331 4.51403 8.48597 4.72669 8.7557 4.8265L10.6783 5.53792C11.1072 5.69664 11.1072 6.30336 10.6783 6.46208L8.7557 7.1735C8.48597 7.27331 8.27331 7.48597 8.1735 7.7557L7.46208 9.67828C7.30336 10.1072 6.69665 10.1072 6.53792 9.67828L5.8265 7.7557C5.72669 7.48597 5.51403 7.27331 5.2443 7.1735L3.32172 6.46208C2.89276 6.30336 2.89276 5.69665 3.32172 5.53792L5.2443 4.8265C5.51403 4.72669 5.72669 4.51403 5.8265 4.2443L6.53792 2.32172Z" stroke="currentColor" stroke-width="1.5" />
                  <path d="M14.4039 9.64136L15.8869 11.1244M6 22H7.49759C8.70997 22 9.31617 22 9.86124 21.7742C10.4063 21.5484 10.835 21.1198 11.6923 20.2625L19.8417 12.1131C20.3808 11.574 20.6503 11.3045 20.7944 11.0137C21.0685 10.4605 21.0685 9.81094 20.7944 9.25772C20.6503 8.96695 20.3808 8.69741 19.8417 8.15832C19.3026 7.61924 19.0331 7.3497 18.7423 7.20561C18.1891 6.93146 17.5395 6.93146 16.9863 7.20561C16.6955 7.3497 16.426 7.61924 15.8869 8.15832L7.73749 16.3077C6.8802 17.165 6.45156 17.5937 6.22578 18.1388C6 18.6838 6 19.29 6 20.5024V22Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </GlobalLink>
            </Button>

            <Button
              as-child
              size="icon"
              :href="route('recipients.show', recipient.uuid)"
              class="bg-gray-500 h-8 w-8">
              <Link as="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                  <path d="M8 7H16.75C18.8567 7 19.91 7 20.6667 7.50559C20.9943 7.72447 21.2755 8.00572 21.4944 8.33329C21.9796 9.05942 21.9992 10.0588 22 12M2 21V7.94427C2 6.1278 2 5.21956 2.38032 4.53806C2.65142 4.05227 3.05227 3.65142 3.53806 3.38032C4.21956 3 5.1278 3 6.94427 3C8.10802 3 8.6899 3 9.19926 3.19101C10.3622 3.62712 10.8418 4.68358 11.3666 5.73313L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                  <path d="M22 15H15M22 18H15M17.5 21H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10.707 20C10.707 20 11.3662 15.9522 10.707 15.293C10.0478 14.6338 6 15.293 6 15.293M5 21L10 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </Link>
            </Button>
          </div>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
