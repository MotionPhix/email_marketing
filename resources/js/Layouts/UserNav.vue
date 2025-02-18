<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
  User,
  Settings,
  Key,
  CreditCard,
  LifeBuoy,
  LogOut
} from 'lucide-vue-next'
import { getInitials } from '@/lib/stringUtils'
import { usePage } from '@inertiajs/vue3'

const user = {
  name: `${usePage().props.auth.user.first_name} ${usePage().props.auth.user.last_name}`,
  email: usePage().props.auth.user.email,
  imageUrl: usePage().props.auth.user.profile_photo_url
}
</script>

<template>
  <DropdownMenu :modal="false">
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        class="relative h-8 w-8 rounded-full">
        <Avatar class="h-8 w-8">
          <AvatarImage
            :src="user.imageUrl"
            :alt="user.name"
          />
          <AvatarFallback>
            {{ getInitials(user.name) }}
          </AvatarFallback>
        </Avatar>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent class="w-56" align="end">
      <DropdownMenuLabel class="font-normal">
        <div class="flex flex-col space-y-1">
          <p class="text-sm font-medium leading-none">{{ user.name }}</p>
          <p class="text-xs leading-none text-muted-foreground">
            {{ user.email }}
          </p>
        </div>
      </DropdownMenuLabel>

      <DropdownMenuSeparator />

      <DropdownMenuGroup>
        <DropdownMenuItem as-child>
          <Link as="button" :href="route('profile.show')" class="w-full">
            <User class="mr-2 h-4 w-4" />
            <span>Profile</span>
            <DropdownMenuShortcut>⇧⌘P</DropdownMenuShortcut>
          </Link>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <Link as="button" :href="route('settings.index')" class="w-full">
            <Settings class="mr-2 h-4 w-4" />
            <span>Settings</span>
            <DropdownMenuShortcut>⌘S</DropdownMenuShortcut>
          </Link>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <Link as="button" :href="route('api-tokens.index')" class="w-full">
            <Key class="mr-2 h-4 w-4" />
            <span>API Tokens</span>
          </Link>
        </DropdownMenuItem>
      </DropdownMenuGroup>

      <DropdownMenuSeparator />

      <DropdownMenuGroup>
<!--        <DropdownMenuItem as-child>-->
<!--          <Link :href="route('billing.index')" class="w-full">-->
<!--            <CreditCard class="mr-2 h-4 w-4" />-->
<!--            <span>Billing</span>-->
<!--          </Link>-->
<!--        </DropdownMenuItem>-->

        <DropdownMenuItem as-child>
          <a href="https://help.emailmarketing.com" target="_blank" class="w-full">
            <LifeBuoy class="mr-2 h-4 w-4" />
            <span>Support</span>
          </a>
        </DropdownMenuItem>
      </DropdownMenuGroup>

      <DropdownMenuSeparator />

      <DropdownMenuItem as-child>
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="w-full">
          <LogOut class="mr-2 h-4 w-4" />
          <span>Log out</span>
          <DropdownMenuShortcut>⇧⌘Q</DropdownMenuShortcut>
        </Link>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
