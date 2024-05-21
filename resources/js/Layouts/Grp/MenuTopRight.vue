<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { useLayoutStore } from '@/Stores/layout'
import { router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, inject } from 'vue'
import { useLiveUsers } from '@/Stores/active-users'
import SearchBar from "@/Components/SearchBar.vue"
import Image from '@/Components/Image.vue'
import Popover from '@/Components/Popover.vue'
import NotificationList from '@/Components/NotificationList/NotificationList.vue'

import Button from "@/Components/Elements/Buttons/Button.vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import Profile from '@/Pages/Grp/Profile.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSignOutAlt } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSignOutAlt)

const props = defineProps<{
    urlPrefix: string
}>()

const layout = inject('layout', layoutStructure)

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

            if( ( isUserMac ? event.metaKey : event.ctrlKey ) && event.key === 'k') {
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
const notifications = layoutStore.user.notifications

</script>

<template>
    <!-- Avatar Group -->
    <div class="flex justify-between gap-x-2">
        <div class="flex items-center gap-x-1">
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
            <div class="relative mx-2 flex items-center">
                <Popover width="w-full">
                    <template #button>
                        <div class="text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <span class="sr-only">{{ trans("Logout") }}</span>
                            <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
                        </div>
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-96">
                            <NotificationList :messages="notifications" />
                        </div>
                    </template>
                </Popover>
            </div>

            <!-- Button: Logout -->
            <div class="relative">
                <Popover width="w-full">
                    <template #button>
                        <FontAwesomeIcon icon='fal fa-sign-out-alt' class='text-red-400 hover:text-red-500' fixed-width aria-hidden='true' size="lg" />
                    </template>

                    <template #content="{ close: closed }">
                        <div class="min-w-32 flex flex-col justify-center gap-y-2">
                            <div class="whitespace-nowrap text-gray-500 text-xs">Are you sure want to logout?</div>
                            <div class="mx-auto">
                                <Button @click="logoutAuth()" label="Yes, Logout" type="negative" />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>

            <!-- Button: Logout -->
            <div @click="layout.stackedComponents.push(Profile)"
                class="flex max-w-xs overflow-hidden items-center rounded-full bg-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
                <span class="sr-only">{{ trans("Open user menu") }}</span>
                <Image class="h-8 w-8 rounded-full" :src="layoutStore.user.avatar_thumbnail" alt="" />
            </div>
        </div>

    </div>
</template>
