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
import { faBoxUsd, faUsersCog, faLightbulb, faUserHardHat, faUser, faInventory, faConveyorBeltAlt, faChevronDown, faPalletAlt } from "@fal"
import { useLayoutStore } from "@/Stores/layout.js"
// import NavigationExpandable from '@//Layouts/NavigationExpandable.vue'
import NavigationSimple from '@/Layouts/NavigationSimple.vue'

import { get } from "lodash"
import NavigationGroup from "./NavigationGroup.vue"
library.add(faBoxUsd, faUsersCog, faLightbulb, faUserHardHat, faUser, faUsersCog, faInventory, faConveyorBeltAlt, faChevronDown, faPalletAlt)

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
const generateNavigationName = (navKey: string) => {
    return navKey.split('_')[0].slice(0, -1)
}

const iconList: {[key: string]: string} = {
    shop: 'fal fa-store-alt',
    warehouse: 'fal fa-warehouse-alt',
    fulfilment: 'fal fa-pallet-alt',
}

// console.log(route().v())

// Generate string 'shop' to 'currentShop'
const generateCurrentString = (str: string) => {
    return 'current' + str.charAt(0).toUpperCase() + str.slice(1)
}

</script>

<template>
    <nav class="isolate relative flex flex-grow flex-col pt-3 pb-4 px-2 h-full overflow-y-auto custom-hide-scrollbar flex-1 space-y-1" aria-label="Sidebar">
        <!-- LeftSidebar: Org -->
        <!-- <span class="text-white">{{ layout.currentShop }} -- {{ layout.currentWarehouse }}</span> -->
        <template v-if="get(layout, ['navigation', 'org', layout.currentParams.organisation], false)">
            <template v-for="(orgNav, itemKey) in layout.navigation.org[layout.currentParams.organisation]" :key="itemKey" >
                <!-- shops_index, warehouses_index, fulfilments_index -->
                <template v-if="itemKey == 'shops_index' || itemKey == 'warehouses_index' || itemKey == 'fulfilments_index'">
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

                    <!-- Fulfilments index (if the fulfilment lenght more than 1) -->
                    <template v-if="itemKey == 'fulfilments_index' && (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length || 0) > 1">
                        <NavigationSimple v-if="!layout.organisationsState?.[layout.currentParams.organisation]?.currentFulfilment"
                            :nav="orgNav"
                            :navKey="itemKey"
                        />
                    </template>
                </template>

                <!-- shops_navigation or warehouses_navigation or fulfilments_navigation -->
                <template v-else-if="itemKey == 'shops_navigation' || itemKey == 'warehouses_navigation' || itemKey == 'fulfilments_navigation'">
                    <template v-if="itemKey == 'shops_navigation' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length">
                        <!-- Shops: ({{ layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length }})
                        {{ layout.organisationsState?.[layout.currentParams.organisation]?.currentShop }} -->
                        <!-- If: Shops length is 1 (show the subNav directly) -->
                        <NavigationSimple v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length === 1"
                            :nav="Object.values(Object.values(orgNav)[0])[0]"
                            :navKey="generateNavigationName(Object.keys(orgNav)[0])"
                        />
                        
                        <template v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateCurrentString(generateCurrentString(itemKey))]">
                            <NavigationGroup
                                :orgNav="orgNav"
                                :itemKey="generateNavigationName(itemKey)"
                                :icon="iconList[generateNavigationName(itemKey)]"
                            />
                        </template>
                    </template>
                    
                    <template v-if="itemKey == 'warehouses_navigation' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length">
                        <!-- Warehouses: ({{ layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length }})
                        {{ layout.organisationsState?.[layout.currentParams.organisation]?.currentWarehouse }} -->
                        <!-- If: Warehouses length is 1 -->
                        <NavigationSimple v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length === 1"
                            :nav="Object.values(Object.values(orgNav)[0])[0]"
                            :navKey="generateNavigationName(Object.keys(orgNav)[0])"
                        />

                        <!-- Else: Warehouses length more than 1 -->
                        <NavigationGroup v-else
                            :orgNav="orgNav"
                            :itemKey="generateNavigationName(itemKey)"
                            :icon="iconList[generateNavigationName(itemKey)]"
                        />
                    </template>
                    
                    <template v-if="itemKey == 'fulfilments_navigation' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length">
                        <!-- Fulfilments: ({{ layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length }})
                        {{ layout.organisationsState?.[layout.currentParams.organisation]?.currentFulfilment }} -->
                        <!-- If Fulfilment length is 1 -->
                        <template v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length === 1">
                            <!-- <NavigationSimple v-for="nav, navKey in Object.values(orgNav)[0]"
                                :nav="nav"
                                :navKey="navKey"
                            /> -->

                            <NavigationGroup
                                :orgNav="orgNav"
                                :itemKey="generateNavigationName(itemKey)"
                                :icon="iconList[generateNavigationName(itemKey)] || ''"
                            />
                        </template>

                        <!-- Else: Fulfilment length more than 1 -->
                        <NavigationGroup v-else
                            :orgNav="orgNav"
                            :itemKey="generateNavigationName(itemKey)"
                            :icon="iconList[generateNavigationName(itemKey)] || ''"
                        />
                    </template>
                </template>

                <!-- Simple Navigation: HR, Procurement, etc -->
                <template v-else>
                    <NavigationSimple
                        :nav="orgNav"
                        :navKey="itemKey"
                    />
                </template>
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
