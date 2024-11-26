<script setup>
defineProps({
  design: {
    type: String,
    default: ''
  }
})
</script>

<template>

  <GlobalModal>

    <section v-html="design"/>

  </GlobalModal>

</template>
