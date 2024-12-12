<script setup lang="ts">
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '@/Components/ui/avatar'
import { Button } from '@/Components/ui/button'
import {Link} from '@inertiajs/vue3'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import {UserIcon, SettingsIcon} from "lucide-vue-next";
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        class="relative h-10 w-10 rounded-full">
        <Avatar class="h-10 w-10">

          <AvatarImage
            :src="$page.props.auth.user.profile_photo_url"
            :alt="$page.props.auth.user.name" />

          <AvatarFallback>
            {{ $page.props.auth.user.name }}
          </AvatarFallback>

        </Avatar>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent class="w-56" align="end">
      <DropdownMenuLabel class="font-normal flex">
        <div class="flex flex-col space-y-1">
          <p class="font-medium leading-none">
            {{ $page.props.auth.user.name }}
          </p>

          <p class="text-xs leading-none text-muted-foreground">
            {{ $page.props.auth.user.email }}
          </p>
        </div>
      </DropdownMenuLabel>

      <DropdownMenuSeparator />

      <DropdownMenuGroup>
        <DropdownMenuItem as-child
          :href="route('profile.show')">
          <Link as="button" class="w-full">
            <UserIcon />
            Profile
          </Link>
        </DropdownMenuItem>

        <DropdownMenuItem as-child
          :href="route('settings.index')">
          <Link as="button" class="w-full">
            <SettingsIcon />
            Settings
          </Link>
        </DropdownMenuItem>
      </DropdownMenuGroup>

      <DropdownMenuSeparator />

      <DropdownMenuItem as-child>
        <Link
          as="button"
          method="post"
          :href="route('logout')" class="w-full">
          Log out
          <DropdownMenuShortcut>⇧⌘Q</DropdownMenuShortcut>
        </Link>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
