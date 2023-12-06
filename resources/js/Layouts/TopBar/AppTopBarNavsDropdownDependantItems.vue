<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Jul 2023 11:56:51 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed } from "vue";

import { Link } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faTasksAlt, faUser, faUserPlus, faCube, faFolderTree, faFolder, faTruck, faFileInvoiceDollar, faBrowser,
    faWarehouse, faMapSigns
} from '@fal';
import { useLayoutStore } from "@/Stores/layout";
import { capitalize } from "@/Composables/capitalize";

library.add(faTasksAlt, faUser, faUserPlus, faCube, faFolderTree, faFolder, faTruck, faFileInvoiceDollar, faBrowser,
    faWarehouse, faMapSigns
);

const layout = useLayoutStore();

const generateRoute = (scope, menuLink) => {

    if (scope == "shops") {
        if (layout.currentShopData.slug) {
            return route(menuLink.route.selected, layout.currentShopData.slug);
        }
        return route(menuLink.route.all);
    }
    if (scope == "websites") {
        if (layout.currentWebsiteData.slug) {
            return route(menuLink.route.selected, layout.currentWebsiteData.slug);
        }
        return route(menuLink.route.all);
    }
    if (scope == "warehouses") {
        if (layout.currentWarehouseData.slug) {
            return route(menuLink.route.selected, layout.currentWarehouseData.slug);
        }
        return route(menuLink.route.all);
    }

    return route(menuLink.route.all);
};

const generateLabel = (scope, menuLink) => {

    if (typeof menuLink.label === "object" && menuLink.label !== null) {
        if (
            (scope == "shops" && layout.currentShopData.slug) ||
            (scope == "websites" && layout.currentWebsiteData.slug) ||
            (scope == "warehouses" && layout.currentWarehouseData.slug)

        ) {
            return menuLink.label.selected;
        }
        return menuLink.label.all;

    }
    return menuLink.label;

};

const getScopeCount = (scope) => {
    if (scope == "shops") {
        return layout.shopsInDropDown.length;
    }
    if (scope == "websites") {
        return layout.websitesInDropDown.length;
    }
    if (scope == "warehouses") {
        return layout.warehousesInDropDown.length;
    }

    return 0;
};

const scope = computed(() => layout.navigation?.[layout.currentModule]?.scope);
const menuLinks = computed(() => layout.navigation?.[layout.currentModule]?.topMenu.dropdown.links);


</script>

<template>
    <div v-if="layout.navigation?.[layout.currentModule]?.topMenu.dropdown"
         class="text-sm text-gray-600 inline-flex place-self-center rounded-r justify-center border-solid "
         :class="[getScopeCount(scope)>1 ? 'border border-l-0 border-indigo-300 divide-x divide-gray-300':'']"
    >
        <Link
            v-for="(menuLink) in menuLinks"
            :href="generateRoute(
                  scope,
                  menuLink)"
            :title="capitalize(menuLink.tooltip)"
            class="group flex justify-center items-center cursor-pointer h-7 py-1 space-x-1 px-4"
            :class="[]"
        >


            <FontAwesomeIcon :icon="menuLink.icon"
                             class="w-auto pr-1 group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
                             aria-hidden="true" />
            <span class="hidden lg:inline capitalize whitespace-nowrap">
              {{ generateLabel(
                scope,
                menuLink
            ) }}
             </span>


        </Link>
    </div>

</template>

