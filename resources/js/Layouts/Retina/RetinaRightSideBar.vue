<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 18 Feb 2024 06:55:17 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { useLocaleStore } from "@/Stores/locale"
import { useLayoutStore } from "@/Stores/retinaLayout"
import { useLiveUsers } from '@/Stores/active-users'
import { onMounted } from 'vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTimes, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useTruncate } from '@/Composables/useTruncate'
import { Link } from '@inertiajs/vue3'
import { useIsFutureIsAPast } from '@/Composables/useFormatTime'
library.add(faTimes, faPencil)

const layout = useLayoutStore()

onMounted(() => {
    if (localStorage.getItem('rightSidebar')) {
        // Read from local storage then store to Pinia
        layout.rightSidebar = JSON.parse(localStorage.getItem('rightSidebar') || '')
    }
})

// Remove the active bar on Right Sidebar
const onClickRemoveBar = (tabName: 'activeUsers') => {
    layout.rightSidebar[tabName].show = false
    localStorage.setItem('rightSidebar', JSON.stringify(layout.rightSidebar))
}

</script>

<template>
    <div class="text-xs h-full border-l border-gray-200 space-y-4">
            <!-- Online Users -->
            <div v-if="layout.rightSidebar.activeUsers.show" class="px-2 py-2" key="1">
                <div class="pl-2 pr-1.5 bg-slate-300/80 text-slate-700 text-xs font-semibold rounded flex justify-between leading-none">
                    <span class="py-1">Active Users</span>
                    <div @click="onClickRemoveBar('activeUsers')" class="flex justify-center items-center cursor-pointer px-1.5 text-slate-400 hover:text-slate-600">
                        <FontAwesomeIcon icon='fal fa-times' class='' aria-hidden='true' />
                    </div>
                </div>

                <!-- Looping: User online list -->
                <TransitionGroup name="list" tag="div">
                    <li v-for="(user, index) in useLiveUsers().liveUsers" :key="`${user.id}_${index}`">
                        <template v-if="!(
                            (user.action === 'leave' && useIsFutureIsAPast(user?.last_active, 300)) ||
                            (user.action === 'logout' && useIsFutureIsAPast(user?.last_active, 3)))
                        ">
                            <Link :href="user.current_page?.url || '#'"
                                class="text-slate-700 pl-2.5 pr-1.5 flex justify-start items-center py-1 gap-x-2.5"
                            >
                                <div class="flex items-center gap-y-0.5 gap-x-1 truncate"
                                    :class="[
                                        {'text-red-500': user.action === 'logout'},
                                        {'text-gray-400': user.action === 'leave'},
                                    ]"
                                >
                                    <span class="leading-none capitalize font-semibold">{{ useTruncate(user?.username, 10) }}</span>
                                    <span class="leading-none">-</span>
                                    <div class="flex items-center gap-x-0.5">
                                        <FontAwesomeIcon v-if="user.current_page?.icon_left?.icon" :icon='user.current_page?.icon_left.icon' fixed-width :class='user.current_page?.icon_left.class' aria-hidden='true' />
                                        <div class="relative">
                                            <Transition name="spin-to-down"><div :key="user?.current_page?.label || 'Unknown'" class="opacity-80 whitespace-nowrap leading-3 text-[10px] capitalize truncate">{{ user?.current_page?.label || 'Unknown' }}</div></Transition>
                                        </div>
                                        <FontAwesomeIcon v-if="user.current_page?.icon_right?.icon" :icon='user.current_page?.icon_right.icon' fixed-width :class='user.current_page?.icon_right.class' aria-hidden='true' />
                                    </div>
                                </div>
                            </Link>
                        </template>
                    </li>
                </TransitionGroup>
            </div>

        <!-- Add new here -->
    </div>
</template>
