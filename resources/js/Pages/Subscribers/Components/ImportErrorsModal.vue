<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'

defineProps<{
  isOpen: boolean
  errors: Array<{
    row: number
    errors: Record<string, string[]>
    data: Record<string, any>
  }>
  onClose: () => void
}>()
</script>

<template>
  <Dialog :open="isOpen" @update:open="onClose">
    <DialogContent class="max-w-4xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Import Errors</DialogTitle>
        <DialogDescription>
          The following rows could not be imported. Please correct the errors and try again.
        </DialogDescription>
      </DialogHeader>

      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Row</TableHead>
            <TableHead>Data</TableHead>
            <TableHead>Errors</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="error in errors" :key="error.row">
            <TableCell>{{ error.row }}</TableCell>
            <TableCell>
              <div class="space-y-1">
                <div v-for="(value, key) in error.data" :key="key">
                  <span class="font-medium">{{ key }}:</span> {{ value }}
                </div>
              </div>
            </TableCell>
            <TableCell>
              <div class="space-y-1">
                <div v-for="(messages, field) in error.errors" :key="field">
                  <Badge variant="destructive" class="mb-1">{{ field }}</Badge>
                  <ul class="list-disc list-inside">
                    <li v-for="message in messages" :key="message" class="text-sm text-red-600">
                      {{ message }}
                    </li>
                  </ul>
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </DialogContent>
  </Dialog>
</template>
