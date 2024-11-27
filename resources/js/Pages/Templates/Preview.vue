<script setup>
defineProps({
  content: {
    type: String,
    default: ''
  }
})
</script>

<template>

  <GlobalModal>

    <section v-html="content" />

  </GlobalModal>

</template>
