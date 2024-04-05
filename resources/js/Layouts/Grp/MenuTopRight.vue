<script setup lang='ts'>
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { trans } from 'laravel-vue-i18n'
import { useLayoutStore } from '@/Stores/layout'
import { router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import { useLiveUsers } from '@/Stores/active-users'
import SearchBar from "@/Components/SearchBar.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Image from '@/Components/Image.vue'
import Popover from '@/Components/Popover.vue'
import NotificationList from '@/Components/NotificationList/NotificationList.vue'

const props = defineProps<{
    urlPrefix: string
}>()

const layoutStore = useLayoutStore()
const showSearchDialog = ref(false)

const logoutAuth = () => {
    router.post(route(props.urlPrefix + 'logout'))

    const dataActiveUser = {
        ...layoutStore.user,
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

onMounted(() => {
    if (typeof window !== 'undefined') {
        document.addEventListener('keydown', (event) => {

            if( ( isUserMac?event.metaKey : event.ctrlKey) && event.key === 'k') {
                event.preventDefault()
                showSearchDialog.value = !showSearchDialog.value
            }
        })
    }
})

onUnmounted(() => {
    document.removeEventListener('keydown', () => false)
})

const isUserMac = navigator.platform.includes('Mac')  // To check the user's Operating System

</script>

<template>
    <!-- Avatar Group -->
    <div class="flex justify-between gap-x-2">
        <div class="flex items-center">
            <!-- Button: Search -->
            <button @click="showSearchDialog = !showSearchDialog" id="search"
                class="h-7 w-fit flex items-center justify-center gap-x-3 ring-1 ring-gray-300 rounded-md px-3 text-gray-500 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <span class="sr-only">{{ trans("Search") }}</span>
                <FontAwesomeIcon aria-hidden="true" size="sm" icon="fa-regular fa-search" />
                <div class="whitespace-nowrap flex items-center justify-end text-gray-500/80 tracking-tight space-x-1">
                    <span v-if="isUserMac" class="ring-1 ring-gray-400 bg-gray-100 px-2 leading-none text-xl rounded">⌘</span>
                    <span v-else class="ring-1 ring-gray-400 bg-gray-100 px-2 py-0.5 text-xs rounded">Ctrl</span>
                    <span class="ring-1 ring-gray-400 bg-gray-100 px-1.5 py-0.5 text-xs rounded">K</span>
                </div>
                <SearchBar :isOpen="showSearchDialog" @close="(e) => showSearchDialog = e" />
            </button>

            <!-- Button: Notifications -->
            <div class="relative">
                <Popover width="w-full">
                    <template #button>
                        <button type="button"
                            class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 mr-3 ml-3">
                            <span class="sr-only">{{ trans("View notifications") }}</span>
                            <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
                        </button>
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-96">
                            <NotificationList />
                        </div>
                    </template>
                </Popover>
            </div>
        </div>

        <!-- Avatar Button -->
        <Menu as="div" class="relative">
            <MenuButton id="avatar-thumbnail"
                class="flex max-w-xs overflow-hidden items-center rounded-full bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500">
                <span class="sr-only">{{ trans("Open user menu") }}</span>
                <Image class="h-8 w-8 rounded-full" :src="layoutStore.user.avatar_thumbnail" alt="" />
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
    </div>
</template>