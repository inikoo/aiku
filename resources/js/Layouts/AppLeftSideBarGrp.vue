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
import { faBoxUsd, faUsersCog, faLightbulb, faUserHardHat, faUser, faInventory } from "@fal"
import { useLayoutStore } from "@/Stores/layout.js"
import { computed } from "vue"
import Image from "@/Components/Image.vue"
import { trans } from 'laravel-vue-i18n';
import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
  } from '@headlessui/vue'
library.add(faBoxUsd, faUsersCog, faLightbulb, faUserHardHat, faUser, faInventory)

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
        <template v-if="route().v().params?.organisation">
            <template v-for="(items, itemKey) in useLayoutStore().navigation.org[route().v().params?.organisation]"
                :key="itemKey"
            >
                <!-- If multi item (Shops or Warehouses) -->
                <div v-if="itemKey == 'shops_navigation' || itemKey == 'warehouses_navigation'"
                    class="pl-4 pb-2"
                    :class="[ itemKey === layout.currentModule ? 'px-0.5' : '', layout.leftSidebar.show ? '' : '', ]"
                >
                    <div class="flex items-center gap-x-1.5 mb-1">
                        <div class="leading-none capitalize text-white font-bold pb-1">{{ itemKey.split('_')[0] }}</div>
                        <hr class="w-full border border-gray-200">
                    </div>
                    
                    <!-- Looping: Shops -->
                    <template v-for="(shopNavigations, shopIndex) in items" :key="shopIndex">
                        <div class="group flex flex-col justify-center text-sm py-0.5"
                            :class="[ shopIndex === layout.currentModule ? '' : '', layout.leftSidebar.show ? 'pl-3' : '', ]"
                            :aria-current="shopIndex === layout.currentModule ? 'page' : undefined"
                        >
                            <p class="capitalize text-white">{{ shopIndex }}</p>

                            <!-- Looping: Navigation in Shop -->
                            <Link v-for="(shopNavigation, navigationIndex) in shopNavigations"
                                :href="shopNavigation.route?.name ? route(shopNavigation.route.name, shopNavigation.route.parameters) : '#'"
                                class="group flex items-center text-sm py-2"
                                :class="[
                                    navigationIndex === layout.currentModule
                                        ? 'navigationActive px-0.5'
                                        : 'navigation px-1',
                                    layout.leftSidebar.show ? 'px-3' : '',
                                ]"
                                :aria-current="navigationIndex === layout.currentModule ? 'page' : undefined"
                            >
                                <div class="flex items-center px-2">
                                    <FontAwesomeIcon aria-hidden="true" class="flex-shrink-0 h-4 w-4" :icon="shopNavigation.icon"/>
                                </div>
                                <span class="capitalize leading-none whitespace-nowrap" :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                                    {{ shopNavigation.label }}
                                </span>
                            </Link>
                        </div>
                    </template>
                </div>

                <Link v-else
                    :href="items.route?.name ? route(items.route.name, items.route.parameters) : '#'"
                    class="group flex items-center text-sm py-2"
                    :class="[
                        itemKey === layout.currentModule
                            ? 'navigationActive px-0.5'
                            : 'navigation px-1',
                        layout.leftSidebar.show ? 'px-3' : '',
                    ]"
                    :aria-current="itemKey === layout.currentModule ? 'page' : undefined"
                >
                    <div class="flex items-center px-2">
                        <FontAwesomeIcon aria-hidden="true" class="flex-shrink-0 h-4 w-4" :icon="items.icon"/>
                    </div>
                    <Transition>
                        <span class="capitalize leading-none whitespace-nowrap" :class="[layout.leftSidebar.show ? 'block md:block' : 'block md:hidden']">
                            {{ items.label }}
                        </span>
                    </Transition>
                </Link>
            </template>
        </template>

        <!-- LeftSidebar: Grp -->
        <Link v-else v-for="(item, itemKey, index) in layout.navigation.grp"
            :key="itemKey + index"
            :href="generateRoute(item)"
            class="group flex items-center text-sm py-2"
            :class="[
                itemKey === layout.currentModule
                    ? 'navigationActive px-0.5'
                    : 'navigation px-1',
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
