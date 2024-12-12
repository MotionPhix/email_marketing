<script setup>
import NavLink from "@/Components/NavLink.vue";
</script>

<template>
  <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
      Dashboard
    </NavLink>

    <NavLink
      href="/recipients"
      :active="route().current('recipients.*')">
      Recipients
    </NavLink>

    <NavLink
      href="/audiences"
      :active="route().current('audiences.*')">
      Audiences
    </NavLink>

    <NavLink
      href="/templates"
      :active="route().current('templates.*')">
      Templates
    </NavLink>

    <NavLink
      href="/campaigns"
      :active="route().current('campaigns.*')">
      Campaigns
    </NavLink>

  </div>
</template>
