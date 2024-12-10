<script setup>
import {ArrowLeftIcon} from "@radix-icons/vue";
import {Button} from "@/Components/ui/button";

const back = () => window.history.back()
</script>

<template>
  <Button size="icon" variant="outline" @click="back">
    <ArrowLeftIcon/>
  </Button>
</template>
