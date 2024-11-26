<script setup lang="ts">
import { Button } from '@/Components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuGroup,
  DropdownMenuSeparator,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import {DotsHorizontalIcon, ExternalLinkIcon} from "@radix-icons/vue";
import {SendHorizontalIcon, Trash2Icon} from "lucide-vue-next";
import {Link} from "@inertiajs/vue3";
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="outline" size="icon">
        <DotsHorizontalIcon/>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent :side-offset="-4" align="end" class="w-40">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>

      <DropdownMenuSeparator/>

      <DropdownMenuGroup>

        <DropdownMenuItem as-child>
          <Link
            method="post"
            class="flex w-full text-left" as="button"
            :href="route('campaigns.send', campaign.uuid)">
            <SendHorizontalIcon class="mr-2 h-4 w-4"/>
            <span class="flex-1">Send</span>
          </Link>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <GlobalLink
            class="flex w-full text-left" as="button"
            :href="route('templates.preview', campaign.template.uuid)">
            <ExternalLinkIcon class="mr-2 h-4 w-4"/>
            <span>Preview</span>
          </GlobalLink>
        </DropdownMenuItem>

      </DropdownMenuGroup>

      <DropdownMenuSeparator/>

      <DropdownMenuGroup>
        <DropdownMenuItem>
          <Trash2Icon class="mr-2 h-4 w-4"/>
          <span>Delete</span>
        </DropdownMenuItem>
      </DropdownMenuGroup>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
