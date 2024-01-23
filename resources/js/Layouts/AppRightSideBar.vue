<script setup lang="ts">
import { useLocaleStore } from "@/Stores/locale"
import { useLayoutStore } from "@/Stores/layout"
import { useLiveUsers } from '@/Stores/active-users'
import { onMounted } from 'vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTimes, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useTruncate } from '@/Composables/useTruncate'
import { Link } from '@inertiajs/vue3'
library.add(faTimes, faPencil)

const layout = useLayoutStore()

onMounted(() => {
    if (localStorage.getItem('rightSidebar')) {
        // Read from local storage then store to Pinia
        layout.rightSidebar = JSON.parse(localStorage.getItem('rightSidebar') ?? '')
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
        <TransitionGroup name="list" tag="ul">
            <!-- Online Users -->
            <li v-if="layout.rightSidebar.activeUsers.show" class="px-2 py-2" key="1">
                <div class="pl-2 pr-1.5 bg-slate-300/80 text-slate-700 text-xs font-semibold rounded flex justify-between leading-none">
                    <span class="py-1">Active Users</span>
                    <div @click="onClickRemoveBar('activeUsers')" class="flex justify-center items-center cursor-pointer px-1.5 text-slate-400 hover:text-slate-600">
                        <FontAwesomeIcon icon='fal fa-times' class='' aria-hidden='true' />
                    </div>
                </div>

                <!-- Looping: User online list -->
                <Link v-for="(user, index) in useLiveUsers().liveUsers" :href="user.current_page?.url || '#'" class="pl-2.5 pr-1.5 flex justify-start items-center py-1 gap-x-2.5">
                    <div class="text-gray-600 flex items-center gap-y-0.5 gap-x-1 truncate">
                        <span class="text-gray-700 leading-none capitalize font-semibold">{{ useTruncate(user?.username, 10) }}</span>
                        <span class="leading-none">-</span>
                        <div class="flex items-center gap-x-0.5">
                            <FontAwesomeIcon v-if="user.current_page?.icon_left?.icon" :icon='user.current_page?.icon_left.icon' fixed-width :class='user.current_page?.icon_left.class' aria-hidden='true' />
                            <span class="text-gray-500 whitespace-nowrap leading-none text-[10px] capitalize truncate">{{ user?.current_page?.label || 'Unknown' }}</span>
                            <FontAwesomeIcon v-if="user.current_page?.icon_right?.icon" :icon='user.current_page?.icon_right.icon' fixed-width :class='user.current_page?.icon_right.class' aria-hidden='true' />
                        </div>
                    </div>
                </Link>
            </li>
        </TransitionGroup>
        
        <!-- Add new here -->
    </div>
</template>
