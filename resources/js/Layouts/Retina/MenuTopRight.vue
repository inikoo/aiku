<script setup lang='ts'>
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { trans } from 'laravel-vue-i18n'
import { router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import { useLiveUsers } from '@/Stores/active-users'
import SearchBar from "@/Components/SearchBar.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Image from '@/Components/Image.vue'

const props = defineProps<{
    urlPrefix: string
}>()

const showSearchDialog = ref(false)

const layout = inject('layout')

const logoutAuth = () => {
    router.post(route(props.urlPrefix + 'logout'))

    const dataActiveUser = {
        ...layout.user,
        name: null,
        last_active: new Date(),
        action: 'logout',
        current_page: {
            label: trans('Logout'),
            url: null,
            icon_left: null,
            icon_right: null,
        },
    }
    window.Echo.join(`grp.live.users`).whisper('otherIsNavigating', dataActiveUser)
    useLiveUsers().unsubscribe()  // Unsubscribe from Laravel Echo
}
// console.log('layout', layout.customer)
</script>

<template>
    <!-- Avatar Group -->
    <div class="flex justify-between gap-x-2">
        <div class="flex">
            <!-- Avatar Button -->
            <Menu as="div" class="relative">
                <MenuButton id="avatar-thumbnail" class="flex gap-x-2 items-center rounded-full">
                    <div class="text-gray-700 text-lg">{{ layout?.customer?.company_name }}</div>
                    <span class="sr-only">{{ trans("Open user menu") }}</span>
                    <div class="h-8 aspect-square rounded-full overflow-hidden border border-gray-300">
                        <Image :src="layout.user.avatar_thumbnail" alt="" />
                    </div>
                </MenuButton>
                
                <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-200 focus:outline-none">
                        <div class="py-1">
                            <MenuItem v-slot="{ active }">
                            <div type="button" @click="router.visit(route(urlPrefix + 'profile.show'))"
                                :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">
                                {{ trans("View profile") }}
                            </div>
                            </MenuItem>
                        </div>
                        <div class="py-1">
                            <MenuItem v-slot="{ active }">
                            <div @click="logoutAuth()"
                                :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']">
                                {{ trans('Logout') }}
                            </div>
                            </MenuItem>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
            <!-- Button: Search -->
            <button @click="showSearchDialog = !showSearchDialog" id="search"
                class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <span class="sr-only">{{ trans("Search") }}</span>
                <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-search" size="lg" />
                <SearchBar :isOpen="showSearchDialog" @close="(e) => showSearchDialog = e" />
            </button>
            <!-- Button: Notifications -->
            <!-- <button type="button"
                    class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <span class="sr-only">{{ trans("View notifications") }}</span>
                <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
            </button> -->
        </div>

        
    </div>
</template>