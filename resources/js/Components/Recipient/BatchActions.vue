<script setup lang="ts">
import {ref} from 'vue'
import {router} from '@inertiajs/vue3'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import {Button} from '@/Components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/Components/ui/dialog'
import {MoreHorizontal, Trash2, Download} from 'lucide-vue-next'
import {toast} from "vue-sonner";

interface Props {
  selectedRecipients: number[]
}

const props = defineProps<Props>()
const showDeleteDialog = ref(false)

const handleDelete = async () => {
  try {
    await router.get(route('recipients.batch', {
      action: 'delete',
      recipients: props.selectedRecipients.join(',')
    }), {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Recipients deleted')

        showDeleteDialog.value = false
      },
      onError: (errors) => {
        console.log(errors)
      }
    });
  } catch (error) {
    console.error('Failed to delete recipients:', error)
  }
}

const handleExport = async (format: 'pdf' | 'excel' | 'csv') => {
  try {
    await window.open(route('recipients.batch', {
      action: `export_${format}`,
      recipients: props.selectedRecipients.join(',')
    }), '_blank')

  } catch (error) {
    console.error(`Failed to export recipients as ${format}:`, error)
  }
}
</script>

<template>
  <Dialog v-model:open="showDeleteDialog">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button
          size="icon"
          variant="outline"
          :disabled="selectedRecipients.length === 0">
          <MoreHorizontal/>
        </Button>
      </DropdownMenuTrigger>

      <DropdownMenuContent align="end" class="w-48">
        <DialogTrigger asChild>
          <DropdownMenuItem class="text-destructive">
            <Trash2 class="mr-2 h-4 w-4"/>
            Delete
          </DropdownMenuItem>
        </DialogTrigger>

        <DropdownMenuItem
          @click="handleExport('pdf')">
          <Download class="mr-2 h-4 w-4"/>
          Export PDF
        </DropdownMenuItem>

        <DropdownMenuItem
          @click="handleExport('excel')">
          <Download class="mr-2 h-4 w-4"/>
          Export Excel
        </DropdownMenuItem>

        <DropdownMenuItem
          @click="handleExport('csv')">
          <Download class="mr-2 h-4 w-4"/>
          Export CSV
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>

    <DialogContent class="rounded-lg px-4 sm:mx-0 mx-auto max-w-sm">
      <DialogHeader>
        <DialogTitle>Are you sure?</DialogTitle>
        <DialogDescription>
          This action cannot be undone. This will permanently delete the selected recipients
          and remove their data from our servers.
        </DialogDescription>
      </DialogHeader>

      <DialogFooter class="gap-4">
        <Button
          variant="outline"
          @click="showDeleteDialog = false">
          Cancel
        </Button>

        <Button
          variant="destructive"
          @click="handleDelete">
          Delete Recipients
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
