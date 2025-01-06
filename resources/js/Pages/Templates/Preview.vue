<script setup>
defineProps({
  content: {
    type: String,
    default: ''
  }
})
</script>

<template>

  <GlobalModal
    panel-classes="scrollbar-smooth scrollbar-none rounded-xl max-h-[90vh] overflow-y-auto dark:bg-gray-700 dark:text-gray-100">

    <section v-html="content" />

  </GlobalModal>

</template>
