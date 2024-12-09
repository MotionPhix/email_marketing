<script setup lang="ts">
import { ref } from 'vue';

const {startDate, endDate} = defineProps<{
  startDate: string,
  endDate: string
}>();

const onStartDate = ref(startDate);
const onEndDate = ref(endDate);

const updateDateRange = () => {
  // Emit updated date range to parent
  emit('update', onStartDate.value, onEndDate.value);
};
</script>

<template>
  <div>
    <input type="date" v-model="onStartDate" />
    <input type="date" v-model="onEndDate" />

    <button @click="updateDateRange">Update</button>
  </div>
</template>
