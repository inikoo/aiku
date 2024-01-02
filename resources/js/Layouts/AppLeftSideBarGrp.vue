<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link } from "@inertiajs/vue3"
import { ref, onMounted, onUnmounted } from "vue"
import { router } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import {faChevronLeft} from "@far"
import { faBoxUsd, faUsersCog } from "@fal"
import { useLayoutStore } from "@/Stores/layout.js"
import { computed } from "vue"
import Image from "@/Components/Image.vue"
import { trans } from 'laravel-vue-i18n';

library.add(faChevronLeft, faBoxUsd, faUsersCog)

const layout = useLayoutStore()

const currentIndexModule = computed(() => {
    return Object.keys(layout.groupNavigation).indexOf(layout.currentModule)
})


onMounted(() => {
    if (localStorage.getItem('leftSideBar')) {
        // Read from local storage then store to Pinia
        layout.leftSidebar.show = JSON.parse(localStorage.getItem('leftSideBar') ?? '')
    }
})

const generateRoute = (item) => {
    const scope = item.scope
    if (scope && typeof item.route === "object" && item.route !== null) {
        if (scope == "shops") {
            if (layout.currentShopData.slug) {
                return route(item.route.selected, layout.currentShopData.slug)
            }
            return route(item.route.all)
        }
        if (scope == "websites") {
            if (layout.currentWebsiteData.slug) {
                return route(item.route.selected, layout.currentWebsiteData.slug)
            }
            return route(item.route.all)
        }
        if (scope == "warehouses") {
            if (layout.currentWarehouseData.slug) {
                return route(item.route.selected, layout.currentWarehouseData.slug)
            }
            return route(item.route.all)
        }
    }
    return route(item.route, item.routeParameters)
}

const generateLabel = (item) => {
    const scope = item.scope
    if (typeof item.label === "object" && item.label !== null) {
        if (
            (scope == "shops" && layout.currentShopData.slug) ||
            (scope == "websites" && layout.currentWebsiteData.slug) ||
            (scope == "warehouses" && layout.currentWarehouseData.slug)
        ) {
            return item.label.selected
        }
        return item.label.all
    }
    return item.label
}

// Set LeftSidebar value to local storage
const handleToggleLeftbar = () => {
    localStorage.setItem('leftSideBar', (!layout.leftSidebar.show).toString())
    layout.leftSidebar.show = !layout.leftSidebar.show
}

</script>

<template>
    <div class="mt-11 fixed md:flex md:flex-col md:inset-y-0 lg:mt-10 bg-gradient-to-t from-org-700 to-org-600 h-full text-gray-400 transition-all duration-200 ease-in-out"
        :class="[layout.leftSidebar.show ? 'w-8/12 md:w-48' : 'w-8/12 md:w-10']"
    >
        
        <Link :href="route('grp.dashboard.show')"
            class="flex flex-col justify-center text-indigo-700 font-logo md:hidden py-3 text-center gap-y-2">
            <Image :src="layout.group.logo" class="h-7 md:h-5 shadow" :alt="layout.group.code" />
            <span>{{ layout.group.name }}</span>
        </Link>
        
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

        <nav class="isolate relative flex flex-grow flex-col pb-4 h-full overflow-y-auto custom-hide-scrollbar flex-1 space-y-1" aria-label="Sidebar">
            <!-- LeftSide Links -->
            <Link v-for="(item, itemKey) in layout.navigation.grp"
                :key="itemKey"
                :href="generateRoute(item)"
                class="group flex items-center text-sm font-medium py-2"
                :class="[
                    itemKey === layout.currentModule
                        ? 'navigationActiveAiku px-0.5'
                        : 'navigationAiku px-1',
                    layout.leftSidebar.show ? 'px-3' : '',
                ]"
                :aria-current="itemKey === layout.currentModule ? 'page' : undefined"
            >
                <div class="flex items-center px-2">
                    <FontAwesomeIcon aria-hidden="true" class="flex-shrink-0 h-4 w-4" :icon="item.icon"/>
                </div>
                <Transition>
                    <span class="capitalize leading-none whitespace-nowrap" :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                        {{ generateLabel(item) }}
                    </span>
                </Transition>
            </Link>
        </nav>
    </div>
</template>
