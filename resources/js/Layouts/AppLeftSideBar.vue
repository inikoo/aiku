<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {Link} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
    faUserHardHat,
    faBars,
    faUsersCog,
    faTachometerAltFast,
    faInventory,
    faStoreAlt,
    faUser,
    faIndustry,
    faParachuteBox,
    faDollyEmpty,
    faShoppingCart,
    faAbacus, faChevronDown,
} from '@/../private/pro-light-svg-icons';
import {useLayoutStore} from '@/Stores/layout';

import AppShopNavigation from '@/Layouts/AppShopNavigation.vue';

library.add(
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
    faUserHardHat,
    faBars,
    faUsersCog,
    faTachometerAltFast,
    faInventory,
    faStoreAlt,
    faUser,
    faIndustry,
    faParachuteBox,
    faDollyEmpty,
    faShoppingCart,
    faAbacus,
    faChevronDown,
);

const layout = useLayoutStore();

const currentModule = (route) => route.substring(0, route.indexOf('.'));

</script>

<template>
    <div class="w-40 mt-11 fixed
        md:border-r md:border-gray-200 md:bg-gray-100 md:flex md:flex-col md:inset-y-0
        md:w-10 lg:mt-10
        xl:w-40 xl:pt-0
        2xl:w-56"
    >
        <div class="flex flex-grow flex-col h-screen overflow-y-auto border-r border-gray-200 bg-white pb-4">
            <div class="font-logo md:hidden xl:block py-3 text-center 2xl:text-left 2xl:pl-6">
                {{ layout.tenant.name }}
            </div>

            <div class="md:hidden">
                <AppShopNavigation />
            </div>

            <div class="flex flex-grow flex-col pb-8">
                <nav class="flex-1 space-y-1 bg-white" aria-label="Sidebar">
                    <Link v-for="(item, itemKey) in layout.navigation"
                          :key="itemKey"
                          :href="route(item.route,item['routeParameters'])"
                          :class="[(itemKey===currentModule(route().current())) ?
                               'border-indigo-600 bg-indigo-50 text-indigo-600' :
                               'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                               'group flex items-center border-l-4 text-sm font-medium px-0 xl:px-3 py-2']"
                          :aria-current="(itemKey===currentModule(route().current()))? 'page' : undefined">
                        <font-awesome-icon aria-hidden="true"
                                           :class="[(itemKey===currentModule(route().current())) ?
                                               'text-indigo-500' :
                                               'text-gray-400 group-hover:text-gray-500',
                                               'ml-2 mr-3 flex-shrink-0 h-5 w-5']" :icon="item.icon" size="lg"/>
                        <span class="md:hidden xl:block capitalize">{{ item.name }}</span>
                    </Link>
                </nav>
            </div>
        </div>
    </div>
</template>

