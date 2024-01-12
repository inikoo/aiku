<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 11 Aug 2022 11:08:49 Malaysia Time, Kuala Lumpur, Malaysia
  -  Reformatted: Fri, 03 Mar 2023 12:40:58 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->


<script setup lang="ts">
import { ref } from "vue"
import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    Disclosure
} from "@headlessui/vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SearchBar from "@/Components/SearchBar.vue"
import { initialiseApp } from "@/Composables/initialiseApp"
import { usePage, router, Link } from "@inertiajs/vue3"
import Footer from "@/Components/Footer/Footer.vue"

import { useLayoutStore } from "@/Stores/layout"


import AppTopbar from "@/Layouts/AppTopbar.vue"
import AppLeftSideBar from "@/Layouts/AppLeftSideBar.vue"
import AppRightSideBar from "@/Layouts/AppRightSideBar.vue"
import AppTopBar from "@/Layouts/TopBar/AppTopBar.vue"
import Breadcrumbs from "@/Components/Navigation/Breadcrumbs.vue"
import { trans } from "laravel-vue-i18n"
import Image from "@/Components/Image.vue"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTachometerAltFast, faGlobe } from '@fal'
import { faSearch, faBell } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSearch, faBell, faTachometerAltFast, faGlobe)


initialiseApp()

// router.on("navigate", () => {
//     if (route().params.hasOwnProperty("shop")) {
//         layout.currentShopData = layout.shops[route().params["shop"]]
//     }
//     if (route().params.hasOwnProperty("website")) {
//         layout.currentWebsiteData = layout.websites[route().params["website"]]
//     }
//     if (route().params.hasOwnProperty("warehouse")) {
//         layout.currentWarehouseData = layout.warehouses[route().params["warehouse"]]
//     }
// })

const layout = useLayoutStore()
const sidebarOpen = ref(false)

</script>

<template>
    <div class="relative min-h-full transition-all duration-200 ease-in-out"
        :class="[Object.values(layout.rightSidebar).some(value => value.show) ? 'mr-44' : 'mr-0']">

        <AppTopbar @sidebarOpen="(value: boolean) => sidebarOpen = value" :sidebarOpen="sidebarOpen"
            :logoRoute="`grp.dashboard.show`" urlPrefix="grp." />

        <!-- Section: Breadcrumbs -->
        <Breadcrumbs class="fixed top-11 lg:top-10 z-[19] w-full transition-all duration-200 ease-in-out"
            :class="[layout.leftSidebar.show ? 'left-0 md:left-48' : 'left-0 md:left-10']"
            :breadcrumbs="usePage().props.breadcrumbs ?? []" :navigation="usePage().props.navigation ?? []" />

        <!-- Sidebar: Left -->
        <div class="">
            <!-- Mobile Helper: background to close hamburger -->
            <div class="bg-gray-200/80 fixed top-0 w-screen h-screen z-10 md:hidden" v-if="sidebarOpen"
                @click="sidebarOpen = !sidebarOpen" />
            <AppLeftSideBar class="-left-2/3 transition-all duration-300 ease-in-out z-20 block md:left-[0]"
                :class="{ 'left-[0]': sidebarOpen }" @click="sidebarOpen = !sidebarOpen" />
        </div>

        <!-- Main Content -->
        <main
            class="h-full relative flex flex-col pt-20 md:pt-16 pb-6 text-gray-700 transition-all duration-200 ease-in-out"
            :class="[layout.leftSidebar.show ? 'ml-0 md:ml-48' : 'ml-0 md:ml-10']">
            <slot />
        </main>

        <!-- Sidebar: Right -->
        <AppRightSideBar class="fixed top-16 w-44 transition-all duration-200 ease-in-out"
            :class="[Object.values(layout.rightSidebar).some(value => value.show) ? 'right-0' : '-right-44']" />

    </div>


    <Footer />
</template>


