<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link } from "@inertiajs/vue3"
import { ref, onMounted } from "vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBoxUsd, faUsersCog, faLightbulb, faUserHardHat, faUser, faInventory, faConveyorBeltAlt, faChevronDown } from "@fal"
import { useLayoutStore } from "@/Stores/layout.js"
import NavigationExpandable from '@//Layouts/NavigationExpandable.vue'
import NavigationSimple from '@//Layouts/NavigationSimple.vue'

import { get } from "lodash"
library.add(faBoxUsd, faUsersCog, faLightbulb, faUserHardHat, faUser, faUsersCog, faInventory, faConveyorBeltAlt, faChevronDown)

const layout = useLayoutStore()

onMounted(() => {
    if (localStorage.getItem('leftSideBar')) {
        // Read from local storage then store to Pinia
        layout.leftSidebar.show = JSON.parse(localStorage.getItem('leftSideBar') ?? '')
    }
})

// const generateRoute = (item) => {
//     const scope = item.scope
//     if (scope && typeof item.route === "object" && item.route !== null) {
//         if (scope == "shops") {
//             if (layout.currentShopData.slug) {
//                 return route(item.route.selected, layout.currentShopData.slug)
//             }
//             return route(item.route.all)
//         }
//         if (scope == "websites") {
//             if (layout.currentWebsiteData.slug) {
//                 return route(item.route.selected, layout.currentWebsiteData.slug)
//             }
//             return route(item.route.all)
//         }
//         if (scope == "warehouses") {
//             if (layout.currentWarehouseData.slug) {
//                 return route(item.route.selected, layout.currentWarehouseData.slug)
//             }
//             return route(item.route.all)
//         }
//     }
//     return route(item.route, item.routeParameters)
// }

// const generateLabel = (item) => {
//     const scope = item.scope
//     if (typeof item.label === "object" && item.label !== null) {
//         if (
//             (scope == "shops" && layout.currentShopData.slug) ||
//             (scope == "websites" && layout.currentWebsiteData.slug) ||
//             (scope == "warehouses" && layout.currentWarehouseData.slug)
//         ) {
//             return item.label.selected
//         }
//         return item.label.all
//     }
//     return item.label

// }

// Conver string from 'shops_navigation' to 'shop', etc
// const generateNavigationName = (navKey: string) => {
//     return navKey.split('_')[0].slice(0, -1)
// }

</script>

<template>
    <nav class="isolate relative flex flex-grow flex-col pt-3 pb-4 px-2 h-full overflow-y-auto custom-hide-scrollbar flex-1 space-y-1" aria-label="Sidebar">
        <!-- LeftSidebar: Org -->
        <!-- <span class="text-white">{{ layout.currentShop }} -- {{ layout.currentWarehouse }}</span> -->
        <template v-if="get(layout, ['navigation', 'org', layout.currentParams.organisation], false)">
            <template v-for="(orgNav, itemKey) in layout.navigation.org[layout.currentParams.organisation]" :key="itemKey" >
                <!-- shops_index or warehouses_index -->
                <template v-if="itemKey == 'shops_index' || itemKey == 'warehouses_index'">
                    <!-- Shops index (if the shop lenght more than 1) -->
                    <template v-if="itemKey == 'shops_index' && (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length || 0) > 1">
                        <NavigationSimple v-if="!layout.organisationsState[layout.currentParams.organisation].currentShop"
                            :nav="orgNav"
                            :navKey="itemKey"
                        />
                    </template>

                    <!-- Warehouses index (if the warehouse lenght more than 1) -->
                    <template v-if="itemKey == 'warehouses_index' && (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length || 0) > 1">
                        <NavigationSimple v-if="!layout.organisationsState?.[layout.currentParams.organisation]?.currentWarehouse"
                            :nav="orgNav"
                            :navKey="itemKey"
                        />
                    </template>
                </template>

                <!-- shops_navigation or warehouses_navigation -->
                <template v-else-if="itemKey == 'shops_navigation' || itemKey == 'warehouses_navigation'">
                    <template v-if="itemKey == 'shops_navigation' && layout.organisationsState?.[layout.currentParams.organisation]?.currentShop">
                        <!-- If Shops length is 1 (Show the subnav straighly) -->
                        <template v-if="Object.keys(orgNav || []).length === 1">
                            <NavigationSimple v-for="aaa, aaaindex in orgNav[Object.keys(orgNav)[0]]"
                                :nav="aaa"
                                :navKey="aaaindex"
                            />
                        </template>
                        
                        <!-- If Shops length is not 1 and current warehouse is exist -->
                        <template v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.currentShop">
                            <NavigationSimple v-for="aaa, aaaindex in orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.currentShop]"
                                :nav="aaa"
                                :navKey="aaaindex"
                            />
                        </template>
                    </template>
                    
                    <template v-if="itemKey == 'warehouses_navigation'">
                        <!-- If Warehouse length is 1 (Show the subnav straighly) -->
                        <template v-if="Object.keys(orgNav || []).length === 1">
                            <NavigationSimple v-for="aaa, aaaindex in orgNav[Object.keys(orgNav)[0]]"
                                :nav="aaa"
                                :navKey="aaaindex"
                            />
                        </template>
                        
                        <!-- If warehouse length is not 1 and current warehouse is exist -->
                        <template v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.currentWarehouse">
                            <NavigationSimple v-for="aaa, aaaindex in orgNav[layout.organisationsState?.[layout.currentParams.organisation]?.currentWarehouse]"
                                :nav="aaa"
                                :navKey="aaaindex"
                            />
                        </template>
                    </template>
                </template>

                <template v-else>
                    <!-- {{ itemKey }} -->
                    <NavigationSimple
                        :nav="orgNav"
                        :navKey="itemKey"
                    />
                </template>

                <!-- <template v-if="itemKey == 'shops_navigation' || itemKey == 'warehouses_navigation'">
                    <template v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${generateNavigationName(itemKey)}s`].length === 1">
                        <NavigationSimple
                            :nav="orgNav[Object.keys(orgNav)[0]][generateNavigationName(itemKey)]"
                            :navKey="generateNavigationName(itemKey)"
                        />
                    </template>

                    <NavigationExpandable v-else
                        :subNav="orgNav"
                        :navKey="itemKey"
                    />
                </template>

                <NavigationSimple v-else
                    :nav="orgNav"
                    :navKey="itemKey"
                /> -->

            </template>
        </template>

        <!-- LeftSidebar: Grp -->
        <template v-else>
            <NavigationSimple
                v-for="(grpNav, itemKey) in layout.navigation.grp"
                :nav="grpNav"
                :navKey="itemKey"
            />
        </template>

    </nav>
</template>
