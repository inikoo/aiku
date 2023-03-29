<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 03 Mar 2023 13:49:56 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import {inject} from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
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

const initialiseApp = inject('initialiseApp');
const layout = initialiseApp();


const currentModule = (route) => route.substring(0, route.indexOf('.'));


</script>

<template>
        <div class="mt-10 hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-gray-200 lg:bg-gray-100 lg:pt-0 lg:pb-4">
            <div class="flex flex-grow flex-col overflow-y-auto border-r border-gray-200 bg-white pb-4">
                <div class="flex flex-shrink-0 items-center space-y-3 px-7 ">
                    <img class=" m-auto h-8  mt-2" src="/art/logo-color-trimmed.png" alt="Aiku"/>
                </div>
                <div class="flex flex-grow flex-col">
                    <nav class="flex-1 space-y-1 bg-white" aria-label="Sidebar">


                        <Link v-for="(item, itemKey) in layout.navigation"
                              :key="itemKey"
                              :href="route(item.route,item['routeParameters'])"
                              :class="[(itemKey===currentModule(route().current())) ?
                               'border-indigo-600 bg-indigo-50 text-indigo-600' :
                               'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                               'group flex items-center border-l-4 px-3 py-2 text-sm font-medium']"
                              :aria-current="(itemKey===currentModule(route().current()))? 'page' : undefined">
                            <font-awesome-icon aria-hidden="true"
                                               :class="[(itemKey===currentModule(route().current())) ?
                                               'text-indigo-500' :
                                               'text-gray-400 group-hover:text-gray-500',
                                               'ml-2 mr-3 flex-shrink-0 h-5 w-5']" :icon="item.icon" size="lg"/>
                            {{ item.name }}
                        </Link>

                    </nav>
                </div>
            </div>
        </div>

</template>


