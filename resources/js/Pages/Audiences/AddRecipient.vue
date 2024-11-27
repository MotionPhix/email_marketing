<script setup lang="ts">

defineProps<{
  recipients?: object
}>()
</script>

<template>

  <GlobalModal>
    <form>
      <pre>
        {{recipients}}
      </pre>
    </form>
  </GlobalModal>
</template>
