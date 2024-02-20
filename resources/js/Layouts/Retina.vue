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
    <div class="absolute inset-0 bg-slate-50" />

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
            <div @click="sidebarOpen = !sidebarOpen" class="bg-gray-200/80 fixed top-0 w-screen h-screen z-10 md:hidden" v-if="sidebarOpen" />
            <RetinaLeftSideBar class="-left-2/3 transition-all duration-300 ease-in-out z-20 block md:left-[0]"
                :class="{ 'left-[0]': sidebarOpen }" @click="sidebarOpen = !sidebarOpen" />
        </div>

        <!-- Main Content -->
        <main class="bg-white shadow rounded h-full relative flex flex-col mt-20 md:mt-12 md:mr-2 pb-6 text-gray-700 transition-all duration-200 ease-in-out"
            :class="[layout.leftSidebar.show ? 'ml-0 md:ml-48' : 'ml-0 md:ml-16']">
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

<style lang="scss">
// * {
//     --color-primary: v-bind('layout.app.theme[0]');
// }

/* Navigation: Aiku */
.navigationActive {
    @apply rounded py-2 font-semibold transition-all duration-0 ease-out;
}
.navigation {
    @apply hover:bg-gray-300/40 py-2 rounded font-semibold transition-all duration-0 ease-out;
}

.subNavActive {
    @apply bg-indigo-200/20 sm:border-l-4 sm:border-indigo-100 text-white font-semibold transition-all duration-0 ease-in-out;
}
.subNav {
    @apply hover:bg-white/80 text-gray-100 hover:text-indigo-500 font-semibold transition-all duration-0 ease-in-out
}

.navigationSecondActive {
    @apply transition-all duration-100 ease-in-out;
}
.navigationSecond {
    @apply hover:bg-gray-100 text-gray-400 hover:text-gray-500 transition-all duration-100 ease-in-out
}

.bottomNavigationActive {
    @apply w-5/6 absolute h-0.5 rounded-full bottom-0 left-[50%] translate-x-[-50%] mx-auto transition-all duration-200 ease-in-out;
    background-color: v-bind('layout.app.theme[3]');
}
.bottomNavigation {
    @apply bg-gray-300 w-0 group-hover:w-3/6 absolute h-0.5 rounded-full bottom-0 left-[50%] translate-x-[-50%] mx-auto transition-all duration-200 ease-in-out
}

</style>