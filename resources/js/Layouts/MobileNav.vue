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
import NavLink from "@/Components/NavLink.vue";
</script>

<template>
  <DropdownMenu v-slot="{ open }" :modal="false">
    <DropdownMenuTrigger as-child>

      <Button
        size="icon"
        variant="ghost"
        class="relative h-10 w-10 dark:text-gray-100">
        <svg
          class="h-[1.2rem] w-[1.2rem]"
          stroke="currentColor"
          fill="none"
          viewBox="0 0 24 24">
          <path
            :class="{'hidden': open, 'inline-flex': ! open }"
            d="M4 5L16 5 M4 12L20 12 M4 19L12 19"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round" />
          <path
            :class="{'hidden': ! open, 'inline-flex': open }"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent class="w-56" align="end">
      <DropdownMenuLabel>
        Main Navigation
      </DropdownMenuLabel>

      <DropdownMenuGroup class="space-y-2">
        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            :href="route('dashboard')"
            :active="route().current('dashboard')">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M7 18V16M12 18V15M17 18V13M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M5.99219 11.4863C8.14729 11.5581 13.0341 11.2328 15.8137 6.82132M13.9923 6.28835L15.8678 5.98649C16.0964 5.95738 16.432 6.13785 16.5145 6.35298L17.0104 7.99142" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Dashboard
          </NavLink>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            href="/recipients"
            :active="route().current('recipients.*')">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M4.5 10C4.5 6.22876 4.5 4.34315 5.67157 3.17157C6.84315 2 8.72876 2 12.5 2H14C17.7712 2 19.6569 2 20.8284 3.17157C22 4.34315 22 6.22876 22 10V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H12.5C8.72876 22 6.84315 22 5.67157 20.8284C4.5 19.6569 4.5 17.7712 4.5 14V10Z" stroke="currentColor" stroke-width="1.5" />
              <path d="M4.5 6H2M4.5 12H2M4.5 18H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M15.2748 8.49261C15.2748 9.59715 14.3794 10.4926 13.2749 10.4926C12.1704 10.4926 11.275 9.59715 11.275 8.49261C11.275 7.38808 12.1704 6.49268 13.2749 6.49268C14.3794 6.49268 15.2748 7.38808 15.2748 8.49261Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M9.31982 15.7161C10.3782 14.0868 12.0589 13.4762 13.2749 13.4774C14.491 13.4787 16.1224 14.0868 17.1807 15.7161C17.2492 15.8215 17.268 15.9512 17.2063 16.0607C16.9588 16.4996 16.1903 17.3705 15.6352 17.4296C14.9975 17.4974 13.3291 17.5069 13.2762 17.5072C13.2232 17.5069 11.5034 17.4974 10.8653 17.4296C10.3103 17.3705 9.5418 16.4996 9.29429 16.0607C9.23254 15.9512 9.25139 15.8215 9.31982 15.7161Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Recipients
          </NavLink>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            href="/audiences"
            :active="route().current('audiences.*')">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M5.08069 15.2964C3.86241 16.0335 0.668175 17.5386 2.61368 19.422C3.56404 20.342 4.62251 21 5.95325 21H13.5468C14.8775 21 15.936 20.342 16.8863 19.422C18.8318 17.5386 15.6376 16.0335 14.4193 15.2964C11.5625 13.5679 7.93752 13.5679 5.08069 15.2964Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M13.5 7C13.5 9.20914 11.7091 11 9.5 11C7.29086 11 5.5 9.20914 5.5 7C5.5 4.79086 7.29086 3 9.5 3C11.7091 3 13.5 4.79086 13.5 7Z" stroke="currentColor" stroke-width="1.5" />
              <path d="M17 5L22 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M17 8L22 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M20 11L22 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Audiences
          </NavLink>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            href="/templates"
            :active="route().current('templates.*')">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M9 7H15C18.2998 7 19.9497 7 20.9749 8.02513C22 9.05025 22 10.7002 22 14V15C22 18.2998 22 19.9497 20.9749 20.9749C19.9497 22 18.2998 22 15 22H14C10.7002 22 9.05025 22 8.02513 20.9749C7 19.9497 7 18.2998 7 15V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M2 7L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              <path d="M7 5L7 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            Templates
          </NavLink>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            href="/campaigns"
            :active="route().current('campaigns.*')">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24" width="24" height="24" fill="none">
              <path d="M14.9263 2.91103L8.27352 6.10452C7.76151 6.35029 7.21443 6.41187 6.65675 6.28693C6.29177 6.20517 6.10926 6.16429 5.9623 6.14751C4.13743 5.93912 3 7.38342 3 9.04427V9.95573C3 11.6166 4.13743 13.0609 5.9623 12.8525C6.10926 12.8357 6.29178 12.7948 6.65675 12.7131C7.21443 12.5881 7.76151 12.6497 8.27352 12.8955L14.9263 16.089C16.4534 16.8221 17.217 17.1886 18.0684 16.9029C18.9197 16.6172 19.2119 16.0041 19.7964 14.778C21.4012 11.4112 21.4012 7.58885 19.7964 4.22196C19.2119 2.99586 18.9197 2.38281 18.0684 2.0971C17.217 1.8114 16.4534 2.17794 14.9263 2.91103Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M11.4581 20.7709L9.96674 22C6.60515 19.3339 7.01583 18.0625 7.01583 13H8.14966C8.60978 15.8609 9.69512 17.216 11.1927 18.197C12.1152 18.8012 12.3054 20.0725 11.4581 20.7709Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M7.5 12.5V6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Campaigns
          </NavLink>
        </DropdownMenuItem>
      </DropdownMenuGroup>

      <DropdownMenuSeparator />

      <DropdownMenuLabel>
        Manage Account
      </DropdownMenuLabel>

      <DropdownMenuGroup>
        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            :href="route('profile.show')"
            :active="route().current('profile.*')">
            <UserIcon />
            Profile
          </NavLink>
        </DropdownMenuItem>

        <DropdownMenuItem as-child>
          <NavLink
            is-mobile
            class="w-full"
            :href="route('settings.index')"
            :active="route().current('settings.*')">
            <SettingsIcon />
            Settings
          </NavLink>
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
