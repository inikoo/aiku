<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Feb 2024 08:02:30 Central Standard Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { onMounted } from "vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBoxUsd, faUsersCog, faChartLine, faUserHardHat, faUser, faInventory, faConveyorBeltAlt,
    faChevronDown, faPalletAlt, faAbacus,faCloudRainbow,faShoppingCart,faMountains, faTasksAlt, faTruck } from "@fal"
import { useLayoutStore } from "@/Stores/layout.js"
import NavigationSimple from '@/Layouts/Grp/NavigationSimple.vue'
import { generateNavigationName, generateCurrentString } from '@/Composables/useConvertString'

import { get } from "lodash"
import NavigationGroup from "@/Layouts/Grp/NavigationGroup.vue"
import NavigationHorizontal from "@/Layouts/Grp/NavigationHorizontal.vue"
library.add(faBoxUsd, faUsersCog, faChartLine, faUserHardHat, faUser, faUsersCog, faInventory, faConveyorBeltAlt, faChevronDown, faPalletAlt,
faAbacus, faCloudRainbow,faShoppingCart,faMountains, faTasksAlt, faTruck
)

const layout = useLayoutStore()

onMounted(() => {
    if (localStorage.getItem('leftSideBar')) {
        // Read from local storage then store to Pinia
        layout.leftSidebar.show = JSON.parse(localStorage.getItem('leftSideBar') ?? '')
    }
})

const iconList: { [key: string]: string } = {
    shop: 'fal fa-store-alt',
    warehouse: 'fal fa-warehouse-alt',
    fulfilment: 'fal fa-hand-holding-box',
}

</script>

<template>
    <nav class="isolate relative flex flex-grow flex-col pt-3 pb-4 px-2 h-full overflow-y-auto custom-hide-scrollbar flex-1 gap-y-1.5" aria-label="Sidebar">
        <!-- LeftSidebar: Org -->
        <template v-if="get(layout, ['navigation', 'org', layout.currentParams?.organisation], false)">
            <template v-for="(orgNav, itemKey) in layout.navigation.org[layout.currentParams.organisation]" :key="itemKey" >
                <!-- shops_index, warehouses_index, fulfilments_index -->
                <template v-if="itemKey == 'shops_index' || itemKey == 'warehouses_index' || itemKey == 'fulfilments_index'">
                    <!-- Shops index (if the shop length more than 1) -->
                    <template v-if="itemKey == 'shops_index' && (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length || 0) > 1">
                        <NavigationSimple v-if="!layout.organisationsState[layout.currentParams.organisation].currentShop"
                            :nav="orgNav"
                            :navKey="itemKey"
                        />
                    </template>

                    <!-- Fulfilments index (if the fulfilment length more than 1) -->
                    <template v-if="itemKey == 'fulfilments_index' && (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length || 0) > 1">
                        <NavigationSimple v-if="!layout.organisationsState?.[layout.currentParams.organisation]?.currentFulfilment"
                            :nav="orgNav"
                            :navKey="itemKey"
                        />
                    </template>

                    <!-- Warehouses index (if the warehouse length more than 1) -->
                    <template v-if="itemKey == 'warehouses_index' && (layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length || 0) > 1">
                        <NavigationSimple v-if="!layout.organisationsState?.[layout.currentParams.organisation]?.currentWarehouse"
                            :nav="orgNav"
                            :navKey="itemKey"
                        />
                    </template>
                </template>

                <!-- shops_navigation or warehouses_navigation or fulfilments_navigation -->
                <template v-else-if="itemKey == 'shops_fulfilments_navigation' || itemKey == 'warehouses_navigation'">
                    <template v-if="itemKey == 'shops_fulfilments_navigation'">
                        <NavigationHorizontal
                            :orgNav="orgNav"
                            :itemKey="generateNavigationName(itemKey)"
                            :icon="iconList[generateNavigationName(itemKey)] || ''"
                        />
                        <!-- {{ orgNav }} -->
                    </template>
                    
                    <template v-if="false && itemKey == 'shops_navigation' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length">
                        <!-- If: Shops length is 1 (show the subNav directly) -->
                        <template v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length === 1">
                            <NavigationGroup
                                :orgNav="orgNav"
                                :itemKey="generateNavigationName(itemKey)"
                                :icon="iconList[generateNavigationName(itemKey)] || ''"
                            />
                        </template>

                        <template v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateNavigationName(generateCurrentString(itemKey))]">
                            <NavigationGroup
                                :orgNav="orgNav"
                                :itemKey="generateNavigationName(itemKey)"
                                :icon="iconList[generateNavigationName(itemKey)]"
                            />
                        </template>
                    </template>

                    <template v-if="false && itemKey == 'fulfilments_navigation'
                        && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length
                    ">
                        <!-- If Fulfilment length is 1 -->
                        <template v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length === 1">
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

                    <template v-if="itemKey == 'warehouses_navigation' && layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length">
                        <!-- If: Warehouses length is 1 -->
                        <template v-if="layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length === 1">
                            <!-- <NavigationGroup
                                :orgNav="orgNav"
                                :itemKey="generateNavigationName(itemKey)"
                                :icon="iconList[generateNavigationName(itemKey)] || ''"
                            /> -->

                            <NavigationSimple v-for="(nav, navKey) in Object.values(orgNav)[0]"
                                :nav="nav"
                                :navKey="navKey"
                            />
                        </template>

                        <!-- Else: Warehouses length more than 1 -->
                        <template v-else-if="layout.organisationsState?.[layout.currentParams.organisation]?.[generateNavigationName(generateCurrentString(itemKey))]">
                            <NavigationGroup
                                :orgNav="orgNav"
                                :itemKey="generateNavigationName(itemKey)"
                                :icon="iconList[generateNavigationName(itemKey)]"
                            />
                        </template>
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
