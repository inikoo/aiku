<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 18 Feb 2024 06:25:44 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { usePage } from "@inertiajs/vue3"
import RetinaFooter from "@/Layouts/Retina/RetinaFooter.vue"
import RetinaLeftSideBar from "@/Layouts/Retina/RetinaLeftSideBar.vue"
import RetinaRightSideBar from "@/Layouts/Retina/RetinaRightSideBar.vue"
import RetinaTopBar from "@/Layouts/Retina/RetinaTopBar.vue"
import Breadcrumbs from "@/Components/Navigation/Breadcrumbs.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { initialiseRetinaApp } from "@/Composables/initialiseRetinaApp"
import { useLayoutStore } from "@/Stores/retinaLayout"
import Notification from '@/Components/Utils/Notification.vue'

import {
    faHome,
    faBars,
    faUsersCog,
    faTachometerAltFast,
    faUser,
    faLanguage
} from '@fal'
import { faSearch, faBell } from '@far'
import { ref } from 'vue'


library.add(
    faHome,
    faBars,
    faUsersCog,
    faTachometerAltFast,
    faUser,
    faLanguage,
    faSearch,
    faBell
)

const layout = initialiseRetinaApp()

const layoutState = useLayoutStore()
const sidebarOpen = ref(false)


</script>

<template>
    <div class="relative min-h-full transition-all duration-200 ease-in-out"
        :class="[Object.values(layout.rightSidebar).some(value => value.show) ? 'mr-44' : 'mr-0']">

        <RetinaTopBar @sidebarOpen="(value: boolean) => sidebarOpen = value" :sidebarOpen="sidebarOpen"
            logoRoute="retina.dashboard.show" urlPrefix="retina." />

        <!-- Section: Breadcrumbs -->
        <Breadcrumbs class="fixed top-11 lg:top-10 z-[19] w-full transition-all duration-200 ease-in-out"
            :class="[layout.leftSidebar.show ? 'left-0 md:left-48' : 'left-0 md:left-12']"
            :breadcrumbs="usePage().props.breadcrumbs ?? []" :navigation="usePage().props.navigation ?? []" />

        <!-- Sidebar: Left -->
        <div class="">
            <!-- Mobile Helper: background to close hamburger -->
            <div class="bg-gray-200/80 fixed top-0 w-screen h-screen z-10 md:hidden" v-if="sidebarOpen"
                @click="sidebarOpen = !sidebarOpen" />
            <RetinaLeftSideBar class="-left-2/3 transition-all duration-300 ease-in-out z-20 block md:left-[0]"
                :class="{ 'left-[0]': sidebarOpen }" @click="sidebarOpen = !sidebarOpen" />
        </div>

        <!-- Main Content -->
        <main
            class="h-full relative flex flex-col pt-20 md:pt-16 pb-6 text-gray-700 transition-all duration-200 ease-in-out"
            :class="[layout.leftSidebar.show ? 'ml-0 md:ml-48' : 'ml-0 md:ml-12']">
            <slot />
        </main>

        <!-- Sidebar: Right -->
        <RetinaRightSideBar class="fixed top-16 w-44 transition-all duration-200 ease-in-out"
            :class="[Object.values(layout.rightSidebar).some(value => value.show) ? 'right-0' : '-right-44']" />

    </div>


    <RetinaFooter />

    <!-- Global declaration: Notification -->
    <notifications
        dangerously-set-inner-html
        :max="3"
        width="500"
        classes="custom-style-notification"
        :pauseOnHover="true"    
    >
        <template #body="props">
            <Notification :notification="props" />  
        </template>
    </notifications>
</template>
