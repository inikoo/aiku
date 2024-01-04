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
import { faBoxUsd, faUsersCog, faLightbulb, faUserHardHat } from "@fal"
import { useLayoutStore } from "@/Stores/layout.js"
import { computed } from "vue"
import Image from "@/Components/Image.vue"
import { trans } from 'laravel-vue-i18n';

library.add(faBoxUsd, faUsersCog, faLightbulb, faUserHardHat)

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


</script>

<template>
    

    <nav class="isolate relative flex flex-grow flex-col pb-4 h-full overflow-y-auto custom-hide-scrollbar flex-1 space-y-1" aria-label="Sidebar">
        <!-- LeftSidebar: Org -->
        <Link v-if="layout.organisations.currentOrganisations"
            v-for="(item, itemKey) in layout.navigation.org[layout.organisations.currentOrganisations]"
            :key="itemKey"
            :href="item.route.name ? route(item.route.name, item.route.parameters) : '#'"
            class="group flex items-center text-sm py-2"
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
                    {{ item.label }}
                </span>
            </Transition>
        </Link>

        <!-- LeftSidebar: Grp -->
        <Link v-else v-for="(item, itemKey, index) in layout.navigation.grp"
            :key="itemKey + index"
            :href="generateRoute(item)"
            class="group flex items-center text-sm py-2"
            :class="[
                itemKey === layout.currentModule
                    ? 'navigationActiveAiku px-0.5'
                    : 'navigationAiku px-1',
                layout.leftSidebar.show ? 'px-3' : '',
            ]"
            :aria-current="itemKey === layout.currentModule ? 'page' : undefined"
        >
            <div class="flex items-center px-2">
                <FontAwesomeIcon aria-hidden="true" fixed-witdh class="h-4 w-4" :icon="item.icon"/>
            </div>
            <Transition>
                <span class="capitalize leading-none whitespace-nowrap" :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                    {{ generateLabel(item) }}
                </span>
            </Transition>
        </Link>
    </nav>
</template>
