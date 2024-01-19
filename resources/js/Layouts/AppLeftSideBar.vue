<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { ref } from "vue"

import LeftSidebarNavigation from "@/Layouts/LeftSidebarNavigation.vue"
import LeftSidebarBottomNav from "@/Layouts/LeftSidebarBottomNav.vue"
import { useLayoutStore } from "@/Stores/layout"

import { library } from "@fortawesome/fontawesome-svg-core"
import { faChevronLeft } from "@far"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faChevronLeft)

const layout = useLayoutStore()

// Set LeftSidebar value to local storage
const handleToggleLeftbar = () => {
    localStorage.setItem('leftSideBar', (!layout.leftSidebar.show).toString())
    layout.leftSidebar.show = !layout.leftSidebar.show
}
</script>

<template>
    <div class="mt-11 pb-10 fixed md:flex md:flex-col md:inset-y-0 lg:mt-10 bg-gradient-to-t to-indigo-600 from-indigo-500 h-full text-gray-400 transition-all duration-200 ease-in-out"
        :class="[layout.leftSidebar.show ? 'w-8/12 md:w-48' : 'w-8/12 md:w-10']"
        id="leftSidebar"
    >
        <!-- Toggle: collapse-expand LeftSideBar -->
        <div @click="handleToggleLeftbar"
            class="hidden absolute z-10 right-0 top-2/4 -translate-y-full translate-x-1/2 w-5 aspect-square bg-indigo-500 hover:bg-indigo-600 text-indigo-100 border border-gray-300 rounded-full md:flex md:justify-center md:items-center cursor-pointer"
            :title="layout.leftSidebar.show ? 'Collapse the bar' : 'Expand the bar'"
        >
            <div class="flex items-center justify-center transition-all duration-300 ease-in-out" :class="{'rotate-180': !layout.leftSidebar.show}">
                <FontAwesomeIcon icon='far fa-chevron-left' class='h-[10px] leading-none' aria-hidden='true'
                    :class="layout.leftSidebar.show ? '-translate-x-[1px]' : ''"
                />
            </div>
        </div>

        <div class="flex flex-grow flex-col h-full overflow-y-auto custom-hide-scrollbar pb-4">
            <LeftSidebarNavigation />
        </div>

        <div class="absolute bottom-[68px] w-full">
            <LeftSidebarBottomNav />
        </div>
    </div>
</template>

<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.custom-hide-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.custom-hide-scrollbar {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}</style>
