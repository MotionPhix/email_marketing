<script setup lang="ts">
interface Column {
  key: string
  label: string
  className?: string
}

interface Props {
  columns: Column[]
  items: any[]
}

defineProps<Props>()
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead
          v-for="column in columns"
          :key="column.key"
          :class="column.className"
        >
          {{ column.label }}
        </TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <slot />
    </TableBody>
  </Table>
</template>
